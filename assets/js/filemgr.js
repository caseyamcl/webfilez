
/**
 * @TOOD: Flesh this out better!
 */
function filemgr_get_file_list(theUrl) {

    if (typeof theUrl == 'undefined') {
        theUrl = server_url + current_path;
    }

    $.ajax({
        url: theUrl,
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
            html = html + "<h3 class='filename'>" + data.relpath + "</h3>";
            html = html + "<p class='filesize'>" + data.size + " bytes</p>";
            html = html + "<a href='" + server_url + data.relpath +"?contents=true' target='_blank' title='View/Download File' class='filestream'>View/Download</span>";
            html = html + "<a href='" + server_url + data.relpath +"' class='delete_link' title='Delete File'>Delete</span>";

            $('#filemgr #filedetails').html(html);
            $('#filemgr #filedetails').show();
        }
    });
}

function filemgr_delete_file(e) {
    e.preventDefault();

    if (confirm("Really delete this file?") == false) {
        return;
    }

    //console.log("Will delete: " + $(this).attr('href'));
    $.ajax({
        url: $(this).attr('href'),
        type: 'DELETE',
        dataType: 'json',
        success: function(data) {
            filemgr_get_file_list(); //rebuild directory list
            $('#filemgr #filedetails').hide();
        }
    });

}

function filemgr_build_breadcrumbs(thePath) {

    if (typeof thePath == 'undefined') {
        thePath = current_path;
    }

    var sections = thePath.split('/').clean();
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

function filemgr_add_dir(dirname) {

    if (typeof dirname != 'string') {
        dirname = '';
    }

    var dirname = prompt("Enter folder name", dirname);

    if ( ! dirname) {
        return;
    }

    var matches = dirname.match(/^([a-z0-9 \.-_]+)$/i);
    if (matches && dirname.length < 255) {

        var fullurl = server_url + current_path + dirname;

        $.ajax({
            url: fullurl,
            type: 'PUT',
            dataType: 'json',
            beforeSend: function(jqXHR, settings) {
                jqXHR.setRequestHeader("IsDir", 1);
            },
            success: function(data) {
                //window.location = fullurl;
                filemgr_get_file_list(fullurl);
                filemgr_build_breadcrumbs(current_path + dirname);
            }
        });


    }
    else {
        alert("Invalid folder name.  Only letters, numbers, spaces, dashes, and periods allowed.  Max length is 255 characters");
        filemgr_add_dir(dirname);
    }
}

function filemgr_open_dir(e) {
    e.preventDefault();
    var fullUrl = $(this).attr('href');
    var thePath = fullUrl.substr(server_url.length);

    filemgr_get_file_list(fullUrl);
    filemgr_build_breadcrumbs(thePath);

    //Update global
    current_path = thePath;
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

    filemgr_build_breadcrumbs();
}

/* EOF: filemgr.js */