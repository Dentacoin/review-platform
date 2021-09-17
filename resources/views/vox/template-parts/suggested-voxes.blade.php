@if(!empty($related_voxes) && $suggested_voxes->count())
    <div class="suggested-wrap">
        <div class="section-recent-surveys new-style-swiper">
            <h3 class="taken-title">{!! trans('vox.page.taken-questionnaire.next-surveys-title') !!}</h3>

            <div class="swipe-cont {{ $suggested_voxes->count() > 2 ? 'swiper-container' : '' }}">
                <div class="swiper-wrapper {{ $suggested_voxes->count() <= 2 ? 'flex' : '' }}">
                    @foreach($suggested_voxes as $survey)
                        <div class="swiper-slide" survey-id="{{ $survey->id }}">
                            @include('vox.template-parts.vox-taken-swiper-slider')
                        </div>
                    @endforeach
                </div>
                <div class="swiper-pagination"></div>
            </div>

            <div class="tac">
                <a href="{{ getLangUrl('/') }}" class="blue-button more-surveys">
                    {!! trans('vox.page.taken-questionnaire.see-surveys') !!}
                </a>
            </div>
        </div>
    </div>
@endif