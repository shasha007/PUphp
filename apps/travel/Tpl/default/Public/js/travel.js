function gbcount(o,max){
    var msg = $(o).val();
    var rest = max - msg.length;
    if(rest < 0){
        rest = 0;
        $('#remain').html(0);
        $(o).val(msg.substring(0,max));
        alert('不能超过'+max+'个字!');
    }
    $('#remain').html(rest);
}