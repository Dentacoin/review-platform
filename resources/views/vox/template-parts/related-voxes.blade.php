@if($suggested_voxes->count() || !empty($related_voxes))
    <div class="related-wrap">
        @if(!empty($related_voxes))
            <div class="section-recent-surveys">
                <h3 class="taken-title">{!! trans('vox.page.taken-questionnaire.related-surveys-title') !!}</h3>

                <div class="swipe-cont {{ count($related_voxes) > 2 ? 'swiper-container' : '' }}">
                    <div class="swiper-wrapper {{ count($related_voxes) <= 2 ? 'flex' : '' }}">
                        @foreach($related_voxes as $survey)
                            <div class="swiper-slide" survey-id="{{ $survey->id }}">
                                @include('vox.template-parts.vox-taken-swiper-slider')
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        @else
            <div class="section-recent-surveys">
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
        @endif
    </div>
@endif