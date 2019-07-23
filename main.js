var tempObj;
var day = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
var month = ['Jan', 'Feb', 'March', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
var zip = "60007";
var api = 'http://api.apixu.com/v1/forecast.json?key=ec7a2b5418414b08a1424009190105&q='+zip+'&days=4';
$(document).ready(function(){
  $.ajax({
    url: api,
    type: "POST",
    dataType: "json",
  }).done(function(data){
    var today = new Date().now;
    $(".test-flex").append("<div class='current test-box'><h1 class='temp'>"+data.current.temp_f+String.fromCharCode(176)+"</h1><img src='"+data.current.condition.icon+"' class='icon'></div>");
    for(var x = 1; x < 4; x++){
      var date = (data.forecast.forecastday[x].date).split('-');
      var myDate = new Date(data.forecast.forecastday[x].date+'T12:00');
      $(".test-flex").append(
        "<div class='forecast test-box'><h3>"+day[myDate.getDay()]+", "+month[parseInt(date[1])-1]+" "+date[2]+"</h3>"
        +"<h3 class='temp daily_forecast'>"
        +data.forecast.forecastday[x].day.maxtemp_f+String.fromCharCode(176)
        +"</h3><img src='"
        +data.forecast.forecastday[x].day.condition.icon+"' class='icon'></br></div>");
    }
  });
  setInterval(currentDate, 1000);
  function startTime() {
    var today = new Date();
    var h = today.getHours();
    var dd = "AM";
    if(h >= 12){
      h = h-12;
      dd = "PM";
    }
    if(h == 0){
      h = 12;
    }
    var m = today.getMinutes();
    var s = today.getSeconds();
    var d = today.getDate();
    var d_name = today.getDay();
    var mon = today.getMonth();
    var y = today.getFullYear();
    m = checkTime(m);
    s = checkTime(s);
    var t = setTimeout(startTime, 500);
    $(".dateTime").html('<h1 class="time">'+ h + ':' + m + dd +'</h1>'+'<h2 class="date">'+day[d_name] +', '+ month[mon] + ' ' + d +'</h2>');
  }
  function checkTime(i) {
    if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
    return i;
  }
  startTime();
});
