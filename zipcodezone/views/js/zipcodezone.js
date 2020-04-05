var txt = {
    en: {
        add: 'Add condition',
        doc: 'Documentation',
        cancel: 'Cancel',
        support: 'Support'
    },
    fr: {
        add: 'Ajouter une condition',
        doc: 'Documentation',
        cancel: 'Annuler',
        support: 'Contact'
    }
};

$(document).ready(function() {

    // Hack
    $('input[name=]').attr('name', 'id_condition');

    // Show new zone input and texarea
    $('select[name=id_zone]').change(function() {
        if ($(this).find('option:selected').val() == 0) {
            $('input[name=zone_name]').parents('.form-group').show();
        } else {
            $('input[name=zone_name]').parents('.form-group').hide();
        }
    });

    // Show textarea
    $('select[name=id_country]').change(function() {
        if (id_countries_with_letters.indexOf(+$(this).find('option:selected').val()) >= 0) {
            $('input[name=min], input[name=max], input[name=zipcode]').parents('.form-group').hide();
            $('input[name=multiple]').parents('.form-group').hide();
            $('textarea[name=zipcodes]').parents('.form-group').show();
        } else {
            $('textarea[name=zipcodes]').parents('.form-group').hide();
            $('input[name=multiple]').parents('.form-group').show();
            $('input[name=multiple]').trigger('change');
        }
    });

    // Show range or single zipcode inputs
    $('input[name=multiple]').change(function() {
        if (id_countries_with_letters.indexOf(+$('select[name=id_country]').find('option:selected').val()) >= 0) {
            return;
        }
        if ($('input[name=multiple]:checked').val() == 0) {
            $('input[name=min], input[name=max]').parents('.form-group').hide();
            $('input[name=zipcode]').parents('.form-group').show();
        } else {
            $('input[name=min], input[name=max]').parents('.form-group').show();
            $('input[name=zipcode]').parents('.form-group').hide();
        }
    });

    $('input[name=multiple]').trigger('change');
    $('select[name=id_zone]').trigger('change');
    $('select[name=id_country]').trigger('change');

    $('.btn-toolbar li').hide();
    $('.btn-toolbar li #desc-module-translate').parent().show();

    iso = txt[iso_user] ? iso_user : 'en';

    // Toolbar links
    $('.btn-toolbar ul').append('<li><a id="desc-module-add" class="toolbar_btn" href="'+decodeURIComponent(add_link)+'" title="'+txt[iso].add+'"><i class="process-icon-plus"></i><div>'+txt[iso].add+'</div></a></li>');
    $('.btn-toolbar ul').append('<li><a id="desc-module-help" class="toolbar_btn" href="'+decodeURIComponent(doc_link)+'" title="'+txt[iso].doc+'" target="_blank"><i class="process-icon-help"></i><div>'+txt[iso].doc+'</div></a></li>');
    $('.btn-toolbar ul').append('<li><a id="desc-module-support" class="toolbar_btn" href="'+decodeURIComponent(support_link)+'" title="'+txt[iso].support+'" target="_blank"><i class="process-icon-envelope"></i><div>'+txt[iso].support+'</div></a></li>');

    // Panel footer link
    $('.panel-footer').append('<a href="'+decodeURIComponent(cancel_link)+'" class="btn btn-default"><i class="process-icon-cancel"></i> '+txt[iso].cancel+'</a>');
});
