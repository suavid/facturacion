$(document).ready(function() {
    $('#options').mouseenter(function() {
        $('#options').animate({'left': 0}, 'fast', function() {
        });
    });
    $('#options').mouseleave(function() {
        $('#options').animate({'left': -65}, 'fast', function() {
        });
    });
});

function url_handler()
{
    this.params = {};
}

url_handler.prototype.initialize = function( ) {

    var Url = location.href;
    Url = Url.replace(/.*\?(.*?)/, "$1");
    var Variables = Url.split("&");
    for (var i = 0; i < Variables.length; i++)
    {
        Separ = Variables[i].split("=");
        this.params[Separ[0]] = Separ[1]
    }
};

url_handler.prototype.isKey = function(key) {
    if (typeof (this.params[key]) != 'undefined')
        return true;
    else
        return false;
};

url_handler.prototype.getValue = function(key) {
    return this.params[key];
};

function show_hidden_form(event, id)
{
    event.preventDefault();
    $('.black_screen').css('display', 'block');
    $('#' + id).fadeIn('slow', function() {
    });
}

function show_hidden_form_ne(id)
{
    $('.black_screen').css('display', 'block');
    $('#' + id).fadeIn('slow', function() {
    });
}

function hidde_form(event, id)
{
    event.preventDefault();
    $('#' + id).fadeOut("fast", function() {
        $('#' + id).css('display', 'none');
        $('.black_screen').css('display', 'none');
    });
}


/* Envia datos del formulario 'id' al servidor para ser procesados por la url indicada usando el metodo post */
function send_data(id, url, ext_parm, class_, container)
{
    /* variables para parsear los parametros */
    if (id != '' && id != ' ') {
        var form = $('#' + id);
        var input = $('#' + id + ' input');
        var textarea = $('#' + id + ' textarea');
        var select = $('#' + id + ' select');
        var num_input = input.length;
        var num_textarea = textarea.length;
        var num_select = select.length;
    }
    var params = {};

    /* verifica si hay parametros pasados de la forma clave1:valor1,clave2:valor2 */
    if (ext_parm != '' && typeof (ext_parm) != 'undefined')
    {
        var array_data = ext_parm.split(',');
        for (var i = 0; i < array_data.length; i++)
        {
            var elem = array_data[i].split(':');
            params[elem[0]] = elem[1];
        }
    }

    /* verifica si se extraen datos del html por medio de un parametro clase */
    if (class_ != '' && typeof (class_) != 'undefined')
    {
        var class_elem = $('.' + class_);
        for (var i = 0; i < class_elem.length; i++)
        {
            params[$(class_elem[i]).attr('id')] = $(class_elem[i]).html();
        }
    }
    if (id != '' && id != ' ')
    {
        /* obtiene todos los datos de los input */
        for (var i = 0; i < num_input; i++)
        {
            if ($(input[i]).attr("type") != 'radio' && $(input[i]).attr("type") != 'checkbox')
            {
                /* input tipo text o password */
                params[$(input[i]).attr("id")] = $(input[i]).val();
            }
            else if ($(input[i]).attr("type") != 'checkbox')
            {
                /* input tipo radio */
                params[$(input[i]).attr("name")] = $("input[name='" + $(input[i]).attr("name") + "']:checked").val();
            }
            else
            {
                /* input tipo checkbox */
                if ($(input[i]).is(':checked'))
                    params[$(input[i]).attr("id")] = $(input[i]).val();
            }
        }

        /* obtiene los valores de los textarea */
        for (var i = 0; i < num_textarea; i++)
        {
            params[$(textarea[i]).attr("id")] = $(textarea[i]).html();
        }

        /* obtiene los valores de los select (combo box) */
        for (var i = 0; i < num_select; i++)
        {
            params[$(select[i]).attr("id")] = $(select[i]).val();
        }
    }
    /* realiza la peticion */
    var jqxhr = $.post(url, params, function(data) {
        if (container != '' && typeof (container) != 'undefined')
        {
            $('#' + container).html(data);
        }
        else
        {
            if (data != "" && data != " ") {
                alert(data);
            }
            window.location.reload();
        }
    })
}


function tab(pestana, panel) {



    pst = document.getElementById(pestana);
    pnl = document.getElementById(panel);
    psts = document.getElementById('tabs').getElementsByTagName('li');
    pnls = document.getElementById('paneles').getElementsByTagName('div');


    for (i = 0; i < psts.length; i++)
    {
        psts[i].className = '';
    }

    pst.className = 'actual';


    for (i = 0; i < pnls.length; i++)
    {
        pnls[i].style.display = 'none';
    }


    pnl.style.display = 'block';
}