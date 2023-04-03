<link rel="stylesheet" href="https://jvectormap.com/css/jquery-jvectormap-2.0.5.css" type="text/css" media="screen"/>
<div class="row">
            <div class="col-12">
                <h3>Visitor Map</h3>
                <div id="world-map" class="w-100"></div>
            </div>
        </div>


@push('scripts')
  <script src="https://jvectormap.com/js/jquery-jvectormap-2.0.5.min.js"></script>
  <script src="https://jvectormap.com/js/jquery-jvectormap-world-mill.js"></script>
  <script>
  @php
  $code = [];
  $country = App\Models\Visitor::selectRaw('country,country_code, count(country) as totalcountry')->groupBy('country')->groupBy('country_code')->get();
  foreach($country as $con){
      $code[$con->country_code] = $con->totalcountry;
  }
  @endphp
    $(function(){
        var gdpData = @json($country);
        var color = @json($code);
        $('#world-map').vectorMap({
            map: 'world_mill',
            backgroundColor: 'none',
            regionStyle: {
              initial: {
                fill: '#326497'
              },
              hover: {
                  fill: "#326497"
              }},
            series: {
              regions: [{
                values:color,
                scale: ['#01ffb3', '#1568ff', '#affee6', '#ffe318', '#00ff89'],
                normalizeFunction: 'polynomial'
              }]
            },
            onRegionTipShow: function(e, el, code){
                var res = gdpData.find(item => item.country_code == code);
                if(res){
                    el.html(el.html()+ ' ' + res.totalcountry);
                }else{
                    el.html(el.html()+' 0');
                }
            }
        });
    });
  </script>
@endpush
