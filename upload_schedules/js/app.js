// Набор переменных
let select_data;

// Раздел функций
function clearlines(){
    $('#s_head').empty();
    $('#s_content').empty();
    $('#route_name').empty();
}

// Преобразование UNIX даты
function unixToDate(val){
    dateObj = new Date(val * 1000);
    utcString = dateObj.toUTCString();
    time = utcString.slice(-12, -7);
    return time;
}

// Основной раздел
$(document).ready(function() {

// Загрузка select
$.get( "core/request.php", { req_type: "get_select_routes" } )
    .done(function( data ) {
        $("#inputState").append(data);
    });

});

// Загрузка расписания
function get_route(){
    let timeIndex = 0;
    let th_head = '<th scope="col">№ п/п</th><th scope="col">Остановки</th>';
    let r_id = document.getElementById("inputState").value;
    clearlines();
    $.get( "core/request.php", { req_type: "get_routes", uid: r_id } )
        .done(function( data ) {
            console.log( JSON.parse(data) );
            route = JSON.parse(data);

            let route_timetbl = route['timetbl'];
            let route_stops = route['stops'];

            $('#route_name').append(route['name'] + ' [' + route['disc'] + ']');
            if ( route_timetbl.length > 1 ){
                for ( var t in route_timetbl ){
                    route_timetbl_t = route_timetbl[t].t;
                    th_head += '<th scope="col">' + route_timetbl[t].ptrn + '</th>';
                }
                $(".s_head").append(th_head);
            }
            else{
                route_timetbl_t = route_timetbl[0].t;
                th_head += '<th scope="col">' + route_timetbl[0].ptrn + '</th>';
                $(".s_head").append(th_head);
            }

            for (i in route_stops){
                let num_stop = route_stops[i].i + 1;
                let name_stop = route_stops[i].p;
                let th_content = '<th scope="row">' + num_stop + '</th><td>' + name_stop + '</td>';
                if ( route_timetbl.length > 1 ){
                    for (x = route_timetbl.length-1; x >= 0;){
                        console.log(route_timetbl[x].t[timeIndex]);
                        th_content += '<td>'+unixToDate(route_timetbl[x].t[timeIndex])+'</td>';
                        x--;
                    }
                    timeIndex++;
                }else{
                    th_content += '<td>'+unixToDate(route_timetbl_t[i])+'</td>';
                }
                $(".s_content").append('<tr>' + th_content + '</tr>');

            }

        });
}