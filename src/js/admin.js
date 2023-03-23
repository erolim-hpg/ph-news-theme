
(jQuery)(function ($) {
    try {
        $('.colorpicker').wpColorPicker();
    } catch (error) {
        console.error(error);
    }
    
    const wp_inline_edit_function = inlineEditPost.edit;
    
    // we overwrite the it with our own
    inlineEditPost.edit = function (post_id) {
        
        console.log('hello');

        // let's merge arguments of the original function
        wp_inline_edit_function.apply(this, arguments);

        // get the post ID from the argument
        if (typeof (post_id) == 'object') { // if it is object, get the ID number
            post_id = parseInt(this.getId(post_id));
        }

        // add rows to variables
        const edit_row = $('#edit-' + post_id)
        const post_row = $('#post-' + post_id)

        const featuredPost = $('.column-featured', post_row).text();

        let featured_newspage = featuredPost.includes('Newspage');
        let featured_spotlight = featuredPost.includes('Spotlight');

        // populate the inputs with column data
        $(':input[name="featured_newspage"]', edit_row).prop('checked', featured_newspage);
        $(':input[name="featured_spotlight"]', edit_row).prop('checked', featured_spotlight);

    }
});

