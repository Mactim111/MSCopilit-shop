@foreach($reviews as $review)
    <x-review-card :review="$review" :is-authenticated="$isAuthenticated" />
@endforeach
