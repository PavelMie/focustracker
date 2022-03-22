
$( document ).ready(function() {
    $('#module_form input').on('change', function() {
        if($('input[name=FOCUSCHECKER_ALL_TIME]:checked', '#module_form').val() == 0){
            $( 'input[name=FOCUSCHECKER_ALL_TIME]' ).parent().parent().parent().next().show();
            $( 'input[name=FOCUSCHECKER_ALL_TIME]' ).parent().parent().parent().next().next().show();
        }else {
            $( 'input[name=FOCUSCHECKER_ALL_TIME]' ).parent().parent().parent().next().hide();
            $( 'input[name=FOCUSCHECKER_ALL_TIME]' ).parent().parent().parent().next().next().hide();
        }

        if($('input[name=FOCUSCHECKER_PAGE_category]:checked', '#module_form').val() == 1){
            $( 'input[name=FOCUSCHECKER_PAGE_category]' ).parent().parent().parent().parent().next().show();
        }else {
            $( 'input[name=FOCUSCHECKER_PAGE_category]' ).parent().parent().parent().parent().next().hide();
        }

        if($('input[name=FOCUSCHECKER_PAGE_product]:checked', '#module_form').val() == 1){
            $( 'input[name=FOCUSCHECKER_PAGE_product]' ).parent().parent().parent().parent().next().next().show();
        }else {
            $( 'input[name=FOCUSCHECKER_PAGE_product]' ).parent().parent().parent().parent().next().next().hide();
        }
    });
});