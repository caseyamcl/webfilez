
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
                var theurl  = server_url + v.path;
                var newhtml = sprintf(
                    "<li title='%s' class='%s'><a href='%s' title='%s'><span class='name'>%s</span></a></li>",
                    k,
                    v.type,
                    theurl,
                    k,
                    filemgr_prep_filename(k)
                );

                html = html + newhtml;
            });

            $('#filemgr #filelist').html(html);
            $('#filemgr #filelist').show();
        }
    });
}

function filemgr_get_file_details(additionalPath) {

    if (typeof additionalPath == 'undefined') {
        additionalPath = '';
    }

    $.ajax({
        url: server_url + current_path + additionalPath,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            html = "<span class='filedetailsclose'>X</span>"
            html = html + "<h3 id='filename'>" + data.relpath + "</h3>";
            html = html + "<a href='" + server_url + data.relpath +"?contents=true' title='View/Download File' class='filestream'>View/Download</span>";

            $('#filemgr #filedetails').html(html);
            $('#filemgr #filedetails').show();
        }
    });
}

function build_breadcrumbs() {
    var sections = current_path.split('/').clean();
    var first = $('#breadcrumbs > li.home').html();

    var output = sprintf("<li class='home'><a href='%s' title='Home'>Home</a></li>", server_url);
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

function filemgr_view_file(e) {
    e.preventDefault();
    filemgr_get_file_details($(this).parent('li').attr('title'));
}

function filemgr_close_file(e) {
    $(this).parent('#filedetails').hide();
}

function initialize_filemgr() {

    if (current_type == 'dir') {
        filemgr_get_file_list();
    }
    else { //is file
      filemgr_get_file_details();
    }

    build_breadcrumbs();
}

/* EOF: filemgr.js */