<?php

declare(strict_types=1);

namespace Modules\Conversation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Conversation\App\Models\Conversation;
use Modules\Conversation\App\Models\ConversationMessage;
use Modules\Listing\Models\Listing;
use Modules\User\App\Models\User;
use Modules\User\App\Support\DemoUserCatalog;

class ConversationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()
            ->whereIn('email', DemoUserCatalog::emails())
            ->orderBy('email')
            ->get()
            ->values();

        if ($users->count() < 2) {
            return;
        }

        ConversationMessage::query()
            ->whereHas('conversation', fn ($query) => $query->whereIn('buyer_id', $users->pluck('id'))->orWhereIn('seller_id', $users->pluck('id')))
            ->delete();

        Conversation::query()
            ->whereIn('buyer_id', $users->pluck('id'))
            ->orWhereIn('seller_id', $users->pluck('id'))
            ->delete();

        foreach ($users as $index => $buyer) {
            $primarySeller = $users->get(($index + 1) % $users->count());
            $secondarySeller = $users->get(($index + 2) % $users->count());

            if (! $primarySeller instanceof User || ! $secondarySeller instanceof User) {
                continue;
            }

            $primaryListing = Listing::query()
                ->where('user_id', $primarySeller->getKey())
                ->where('status', 'active')
                ->orderBy('id')
                ->first();

            $secondaryListing = Listing::query()
                ->where('user_id', $secondarySeller->getKey())
                ->where('status', 'active')
                ->orderBy('id')
                ->first();

            $this->seedConversationThread(
                $primarySeller,
                $buyer,
                $primaryListing,
                $this->messagePayloads($index, false)
            );

            $this->seedConversationThread(
                $secondarySeller,
                $buyer,
                $secondaryListing,
                $this->messagePayloads($index, true)
            );
        }
    }

    private function seedConversationThread(
        User $seller,
        User $buyer,
        ?Listing $listing,
        array $messages
    ): void {
        if (! $listing || (int) $seller->getKey() === (int) $buyer->getKey()) {
            return;
        }

        $conversation = Conversation::updateOrCreate(
            [
                'listing_id' => $listing->getKey(),
                'buyer_id' => $buyer->getKey(),
            ],
            [
                'seller_id' => $seller->getKey(),
                'last_message_at' => now(),
            ]
        );

        $lastMessageAt = null;

        foreach ($messages as $payload) {
            $createdAt = now()->subHours((int) $payload['hours_ago']);
            $sender = ($payload['sender'] ?? 'buyer') === 'seller' ? $seller : $buyer;
            $readAfterMinutes = $payload['read_after_minutes'];
            $readAt = is_numeric($readAfterMinutes) ? $createdAt->copy()->addMinutes((int) $readAfterMinutes) : null;

            $message = new ConversationMessage;
            $message->forceFill([
                'conversation_id' => $conversation->getKey(),
                'sender_id' => $sender->getKey(),
                'body' => (string) $payload['body'],
                'read_at' => $readAt,
                'created_at' => $createdAt,
                'updated_at' => $readAt ?? $createdAt,
            ])->save();

            $lastMessageAt = $createdAt;
        }

        if ($lastMessageAt) {
            $conversation->forceFill([
                'seller_id' => $seller->getKey(),
                'last_message_at' => $lastMessageAt,
                'updated_at' => $lastMessageAt,
            ])->saveQuietly();
        }
    }

    private function messagePayloads(int $index, bool $secondary): array
    {
        $openingMessages = [
            'Is this job still accepting applications?',
            'Can you share the pay range and schedule?',
            'Would this role be open to someone changing careers?',
            'Can you confirm whether bilingual candidates are preferred?',
            'Do you have more detail on training and benefits?',
        ];

        $sellerReplies = [
            'Yes, we are reviewing candidates this week.',
            'The posted range is current and the schedule is flexible.',
            'Yes, transferable experience is welcome.',
            'Bilingual applicants are encouraged but not required.',
            'Paid onboarding is included and benefits depend on hours.',
        ];

        $closingMessages = [
            'Great, I will send my resume today.',
            'Perfect. I saved the job and will apply.',
            'Thanks. That sounds like a strong fit.',
            'Understood. I am interested in next steps.',
            'Nice. I will share this with my workforce coach.',
        ];

        $offset = ($index + ($secondary ? 2 : 0)) % count($openingMessages);

        return [
            ['sender' => 'buyer', 'body' => $openingMessages[$offset], 'hours_ago' => 30 - $index, 'read_after_minutes' => 5],
            ['sender' => 'seller', 'body' => $sellerReplies[$offset], 'hours_ago' => 28 - $index, 'read_after_minutes' => 8],
            ['sender' => 'buyer', 'body' => $closingMessages[$offset], 'hours_ago' => 4 + $index, 'read_after_minutes' => $secondary ? 6 : null],
        ];
    }
}
