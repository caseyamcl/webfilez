
/**
 * @TOOD: Flesh this out better!
 */
function filemgr_get_file_list() {

    $.ajax({
        url: server_url,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            html = '';
            $.each(data, function(k, v) {
                html = html + "<li title='" + k + "'><span class='name'>" + filemgr_prep_filename(k) + "</span></li>";
            });

            $('#filemgr #filelist').html(html);
        }
    });

}

function filemgr_prep_filename(fname, limit) {

    if (typeof limit == 'undefined') {
        limit = 20;
    }

    if (fname.length > limit) {

        //If extension
        if (fname.lastIndexOf('.') != -1) {

            basename = fname.slice(0, fname.lastIndexOf('.') - 1);
            extension = fname.slice(fname.lastIndexOf('.'));
            limit = limit - extension.length;
            fname = basename.substr(0, limit-1) + '.' + extension + '&hellip;';
        }
        else {
            fname = fname.substr(0, limit-1) + '&hellip;';
        }

    }

    return fname;

}

function initialize_filemgr() {
    filemgr_get_file_list();
}

/* EOF: filemgr.js */