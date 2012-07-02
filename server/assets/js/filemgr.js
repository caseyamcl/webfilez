
/**
 * @TOOD: Flesh this out better!
 */
function filemgr_get_file_list() {

    $.ajax({
        url: server_url + '',
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function(data) {
            html = '';
            $.each(data, function(k, v) {
                html = html + "<li><span class='name'>" + v + "</span></li>";
            });

            $('#filemgr #filelist').html(html);
        }
    });

}

function initialize_filemgr() {
    filemgr_get_file_list();
}

/* EOF: filemgr.js */