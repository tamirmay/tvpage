function savesearchParams(txtUrl, searchDepth){
    localStorage['myQry_' + (new Date()).getTime()] = [txtUrl, searchDepth];
}

// submit form using $.ajax() method         	
$('#reg-form').submit(function(e){
    e.preventDefault(); // Prevent Default Submission

    var currentForm = $(this);
    var lightBox = $('#light-box');

    savesearchParams(currentForm.find('#txt_url').val(), currentForm.find('#select_depth').val());

    lightBox.addClass('visible');

    $.ajax({
        url: 'crawler.php',
        type: 'POST',
        data: currentForm.serialize() // serialize the form data
    })
    .done(function(data){
        $('#form-content').fadeOut('slow', function(){
            $('#form-content').fadeIn('slow').html(data);
        });
    })
    .fail(function(){
        alert('Ajax Submit Failed ...');	
    })
    .always(function(){
        lightBox.removeClass('visible');
    });
});
