jQuery( document ).ready( function( $ ) { 
    function jlconverttax_display_categories() {
        const select_from = $('#jlconverttax-from-taxonomy');
        const select_to = $('#jlconverttax-to-taxonomy');
        let from_option = 'category';
        let to_option = 'post_tag';
        select_from.on('change', function() {
            from_option = select_from.val();
            to_option = select_to.val();
            $.ajax({
                url     :   jlconverttax_script_ajax_object.ajax_url,   // wp_localize_script -> jlconverttax_script_ajax_object.ajax_url
                method  :   'post',
                dataType:   'json',
                data    :   { action: 'load_categories_by_ajax', 'from-category': from_option, 'to-category': to_option },      // action: function, that is invoked by ajax (full name: jlconverttax_load_categories_by_ajax)
                success :   function(response) {
                                window.location.replace(response);
                },
                error   :   function(){
                                // console.log('connection error ');
                }
            });
        });
        select_to.on('change', function() {
            from_option = select_from.val();
            to_option = select_to.val();
            $.ajax({
                url     :   jlconverttax_script_ajax_object.ajax_url,
                method  :   'post',
                dataType:   'json',
                data    :   { action: 'load_categories_by_ajax', 'from-category': from_option, 'to-category': to_option },
                success :   function(response) {
                                //
                },
                error   :   function(){
                                // console.log('connection error ');
                }
            });
        });
    }
    jlconverttax_display_categories();
});
