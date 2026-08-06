@extends('app::layouts.app')

@section('title', trim((string) ($selectedCategory?->name ?? '')) !== '' ? trim((string) $selectedCategory->name).' Jobs and Compensation' : 'All Jobs and Compensation')

@section('content')
    @include('listing::partials.index-content')
@endsection
