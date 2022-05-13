<h2 class="mont">
    {!! nl2br(trans('trp.page.user.reviews')) !!}
</h2>
@foreach($item->reviews_in_standard() as $review)
    @if($review->user)
        @include('trp.parts.reviews', [
            'review' => $review,
            'is_dentist' => true,
			'hidden' => $loop->iteration > 10,
            'for_profile' => false,
            'current_dentist' => $review->getDentist($item),
        ])
    @endif
@endforeach