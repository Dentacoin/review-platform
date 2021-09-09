<div class="container">
    <h2 class="black-left-line section-title">
        {!! nl2br(trans('trp.page.user.reviews')) !!}
    </h2>
    @foreach($item->reviews_in_standard() as $review)
        @if($review->user)
            @include('trp.parts.reviews', [
                'review' => $review,
                'is_dentist' => true,
                'for_profile' => false,
                'current_dentist' => $review->getDentist($item),
                'my_upvotes' => $my_upvotes,
                'my_downvotes' => $my_downvotes,
            ])
        @endif
    @endforeach
</div>