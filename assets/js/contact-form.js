$(document).ready(function(){

    $('.contact-form-submit').on('click', function(){
        $($(this).data('form-request')).submit();
    });
    

});