<h1>LA Sentinel Jobs Partner Inquiry</h1>

<p><strong>Company name:</strong> {{ $inquiry['company_name'] }}</p>
<p><strong>Contact name:</strong> {{ $inquiry['contact_name'] }}</p>

<p><strong>Contact information:</strong></p>
<p>{!! nl2br(e($inquiry['contact_information'])) !!}</p>

@if(! empty($inquiry['comments']))
<p><strong>Comments:</strong></p>
<p>{!! nl2br(e($inquiry['comments'])) !!}</p>
@endif
