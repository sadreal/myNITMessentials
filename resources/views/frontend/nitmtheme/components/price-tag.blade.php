<div>
    @if(showEcommerce())
        <span class="prise_tag">

               @if ((int)$discount!=0)
                <span class="prev_prise">
                  {{getPriceFormat($price)}}
                  </span>
            @endif

              <span>
            @if ((int)$discount!=0)
                      {{getPriceFormat($discount)}}
                  @else
                      {{getPriceFormat($price)}}
                  @endif

          </span>
</span>
    @endif
</div>
