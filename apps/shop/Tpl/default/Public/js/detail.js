function showTab(i,o){
    $('.cur').removeClass('cur');
    $('#tab1').hide();
    $('#tab2').hide();
    $('#tab3').hide();
    $('#tab'+i).show();
    $(o).parent().addClass('cur');
}