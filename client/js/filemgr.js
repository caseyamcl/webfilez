
/**
 * @TOOD: Flesh this out better!
 */
function filemgr_get_file_list() {

    $.ajax({
        url: server_url + 'getfilelist.php',
        type: 'POST',
        dataType: 'json',
        cache: false,
        success: function(data) {
            html = '';
            $.each(data, function(k, v) {
                html = html + "<li>" + v + "</li>";
            });

            $('#filemgr #filelist').html(html);
        }
    });

}

function initialize_filemgr() {
    filemgr_get_file_list();
}

/* EOF: filemgr.js */