jQuery( document ).ready( function( $ ) { 
    function jlconverttax_display_categories() {
        const select_from = $('#jlconverttax-from-taxonomy');
        const select_to = $('#jlconverttax-to-taxonomy');
        let from_option = 'category';
        let to_option = 'post_tag';
        select_from.on('change', function() {
            from_option = select_from.val();
            $.ajax({
                url     :   jlconverttax_script_ajax_object.ajax_url,   // wp_localize_script -> jlconverttax_script_ajax_object.ajax_url
                method  :   'post',
                dataType:   'json',
                data    :   { action: 'load_categories_by_ajax', 'category': from_option },      // action: function, that is invoked by ajax (full name: jlconverttax_load_categories_by_ajax)
                success :   function(response) {
                                const get_from = $('.from-option');
                                const display_category = $('.display-category');
                                get_from.text(from_option);
                                display_category.empty();
                                display_category.html(response);
                },
                error   :   function(){
                                // console.log('connection error ');
                }
            });
        });
        select_to.on('change', function() {
            to_option = select_to.val();
            $.ajax({
                url     :   jlconverttax_script_ajax_object.ajax_url,
                method  :   'post',
                dataType:   'json',
                data    :   { action: 'load_categories_by_ajax', 'category': to_option },
                success :   function(response) {
                                const get_to = $('.to-option');
                                get_to.text(to_option);
                },
                error   :   function(){
                                // console.log('connection error ');
                }
            });
        });
    }
    jlconverttax_display_categories();
});
