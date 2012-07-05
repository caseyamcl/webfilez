
/**
 * @TOOD: Flesh this out better!
 */
function filemgr_get_file_list() {

    $.ajax({
        url: server_url + current_path,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            html = '';
            $.each(data, function(k, v) {
                var theurl  = server_url + v;
                var newhtml = sprintf("<li title='%s'><a href='%s' title='%s'><span class='name'>%s</span></a></li>", k, theurl, k, filemgr_prep_filename(k));
                html = html + newhtml;
            });

            $('#filemgr #filelist').html(html);
        }
    });

}

function build_breadcrumbs() {
    var sections = current_path.split('/');
    var first = $('#breadcrumbs > li.home').html();

    var output = "<li class='home'>" + first + "</li>";
    var currpath = '';
    $.each(sections, function(k,v) {
        currpath = '/' + currpath + v;
        output = output + "<li class='sep'>&raquo;</li>";
        output = output + sprintf("<li><a href='%s' title='%s'>%s</a></li>", server_url + currpath, server_url + currpath, v);
    });

    $('#breadcrumbs').html(output);
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
    build_breadcrumbs();
}

/* EOF: filemgr.js */