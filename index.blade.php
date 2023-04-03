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


Route::get('visitor', function(){
    
    if(request()->ajax()){
        $ip = request()->ip();
        
        $checkIp = Visitor::where("ip", $ip)->first();
        
        if($checkIp){
            
            date_default_timezone_set($checkIp->timezone);
            $checkIp->local_time = date('y-m-d H:i:s', time());
            $checkIp->browser = request()->header('User-Agent');
            $checkIp->save();
            $checkIp->increment('count');
            
        }else{
            
            $data = [];
            $data['ip'] = $ip;
            $data['count'] = 1;
            $data['browser'] = request()->header('User-Agent');
            $ipinfo = json_decode(file_get_contents('http://ipinfo.io/'.$ip.'/json'));
            $geoplugin = json_decode(file_get_contents('http://www.geoplugin.net/json.gp?ip='. $ip));
            if($ipinfo){
                $data['city'] = $ipinfo->city??null;
                $data['timezone'] = $ipinfo->timezone??null;
                date_default_timezone_set($ipinfo->timezone);
                $data['local_time'] = date('y-m-d H:i:s', time());
            }
            if($geoplugin){
                $data['country'] = $geoplugin->geoplugin_countryName??null;
                $data['continent_name'] = $geoplugin->geoplugin_continentName??null;
                $data['country_code'] = $geoplugin->geoplugin_countryCode??null;
                $data['currency_symbol'] = $geoplugin->geoplugin_currencySymbol_UTF8??null;
            }
            Visitor::create($data);
            
        }
        return 'success';
    }
})->name('visitor');


