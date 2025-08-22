<?php
/*
Plugin Name: Custom Shortcodes
Description: Provides custom shortcodes like [book_posts]
Version: 1.0
Author: Franco Destriza
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access
}

/*
|--------------------------------------------------------------------------
| 2. SHORTCODE TO DISPLAY BOOK POSTS
|--------------------------------------------------------------------------
*/      
function cs_book_posts_shortcode($atts) {
    ob_start();

    // Attributes (default 12 posts)
    $atts = shortcode_atts([
        'count' => 12, 
        'category' => '', // Optional filter
    ], $atts, 'book_posts');

    // Query posts
    $args = [
        'post_type'      => 'post',
        'posts_per_page' => intval($atts['count']),
        'orderby'        => 'date',
        'order'          => 'DESC'
    ];

    if (!empty($atts['category'])) {
        $args['category_name'] = sanitize_text_field($atts['category']);
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<div class="book-posts-wrapper" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:20px;">';
        while ($query->have_posts()) {
            $query->the_post();

            echo '<div class="book-post-card" style="border:1px solid #ddd; padding:15px; border-radius:10px; background:#fff; box-shadow:0 2px 6px rgba(0,0,0,0.1);">';
            
            // Featured Image
            if (has_post_thumbnail()) {
                echo '<div class="book-thumb" style="margin-bottom:10px;">';
                echo get_the_post_thumbnail(get_the_ID(), 'medium', ['style'=>'width:100%; border-radius:8px;']);
                echo '</div>';
            }

            // Title
            echo '<h3 style="margin:10px 0; font-size:18px;"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';

            // Date
            echo '<p style="font-size:14px; color:#666; margin:5px 0;">' . get_the_date() . '</p>';

            // Excerpt
            echo '<p style="font-size:15px; line-height:1.5; color:#444;">' . wp_trim_words(get_the_excerpt(), 20, '...') . '</p>';

            // Custom Fields
            $author = get_post_meta(get_the_ID(), '_meta_meta_value_key', true);
            $gender = get_post_meta(get_the_ID(), '_meta_gender_value_key', true);
            $hobbies = get_post_meta(get_the_ID(), '_meta_hobbies_value_key', true);
            $motto = get_post_meta(get_the_ID(), '_meta_motto_value_key', true);

            echo '<div style="margin-top:10px; font-size:14px; color:#333;">';
            if ($author) {
                echo '<strong>Author:</strong> ' . esc_html($author) . '<br>';
            }
            if ($gender) {
                echo '<strong>Gender:</strong> ' . esc_html($gender) . '<br>';
            }
            if ($hobbies) {
                if (is_array($hobbies)) {
                    echo '<strong>Hobbies:</strong> ' . esc_html(implode(', ', $hobbies)) . '<br>';
                } else {
                    echo '<strong>Hobbies:</strong> ' . esc_html($hobbies) . '<br>';
                }
            }
            if ($motto) {
                echo '<strong>Motto:</strong> ' . esc_html($motto) . '<br>';
            }
            echo '</div>';

            echo '</div>';
        }
        echo '</div>';
        wp_reset_postdata();
    } else {
        echo "<p>No posts found.</p>";
    }

    return ob_get_clean();
}
add_shortcode('book_posts', 'cs_book_posts_shortcode');


// CUSTOM POST TYPE AND META BOX

function meta_add_custom_box() {
	$screens = [ 'post', 'meta_cpt' ];
	foreach ( $screens as $screen ) {
		add_meta_box(
			'meta_box_id',                 // Unique ID
			'Custom Meta Box',             // Box title
			'meta_custom_box_html',        // Content callback, must be of type callable
			$screen                        // Post type
		);
	}
}
add_action( 'add_meta_boxes', 'meta_add_custom_box' );

function meta_custom_box_html( $post ) {
    // Retrieve saved values
    $author_value = get_post_meta( $post->ID, '_meta_meta_value_key', true );
    $gender_value = get_post_meta( $post->ID, '_meta_gender_value_key', true );
    $hobbies_value = get_post_meta( $post->ID, '_meta_hobbies_value_key', true );
    $checked_value = get_post_meta( $post->ID, '_meta_meta_value_key', true );
    $motto_value = get_post_meta( $post->ID, '_meta_motto_value_key', true );
    ?>
    <label for="meta_field">Author</label>
    <label for="meta_field">
    <input type="text" id="meta_field" name="meta_field" value="<?php echo esc_attr($author_value); ?>" />
    <br><br>
    <label>Gender:</label>
    <br><br>    
    <label>
        <input type="radio" name="meta_gender" value="male" <?php checked($gender_value, 'male'); ?> /> Male
    </label>
    <br><br>
    <label>
        <input type="radio" name="meta_gender" value="female" <?php checked($gender_value, 'female'); ?> /> Female
    </label>
    <br><br>
    <label>Hobbies</label>
    <label for="meta_hobbies">
    <br><br>
    <div style="display: flex; flex-direction: column; gap: 10px;">
        <label style="display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" name="meta_hobbies_writing" value="1" <?php checked( $hobbies_value === 'writing' || (is_array($hobbies_value) && in_array('writing', $hobbies_value)) ); ?> />
            Writing
        </label>
        <label style="display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" name="meta_hobbies_painting" value="1" <?php checked( $hobbies_value === 'painting' || (is_array($hobbies_value) && in_array('painting', $hobbies_value)) ); ?> />
            Painting
        </label>
        <label style="display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" name="meta_hobbies_dancing" value="1" <?php checked( $hobbies_value === 'dancing' || (is_array($hobbies_value) && in_array('dancing', $hobbies_value)) ); ?> />
            Dancing
        </label>
        <label style="display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" name="meta_hobbies_hiking" value="1" <?php checked( $hobbies_value === 'hiking' || (is_array($hobbies_value) && in_array('hiking', $hobbies_value)) ); ?> />
            Hiking
        </label>
        <label for ="meta_motto">Motto</label>
        <input type="text" id="meta_motto" name="meta_motto" value="<?php echo esc_attr( get_post_meta( $post->ID, '_meta_motto_value_key', true ) ); ?>" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;" />
    </div>
    <?php
}
function meta_save_postdata( $post_id ) {
    if ( array_key_exists( 'meta_field', $_POST ) ) {
        update_post_meta(
            $post_id,
            '_meta_meta_value_key',
            sanitize_text_field( $_POST['meta_field'] )
        );
    }
    if ( array_key_exists( 'meta_gender', $_POST ) ) {
        update_post_meta(
            $post_id,
            '_meta_gender_value_key',
            sanitize_text_field( $_POST['meta_gender'] )
        );
    }
    if ( array_key_exists( 'meta_hobbies', $_POST ) ) {
        update_post_meta(
            $post_id,
            '_meta_hobbies_value_key',
            array_map('sanitize_text_field', $_POST['meta_hobbies'])
        );
    } else {
        update_post_meta(
            $post_id,
            '_meta_hobbies_value_key',
            []
        );
    }
}
add_action( 'save_post', 'meta_save_postdata' );

function create_custom_post_type() {
    $labels = array(
		'name'                  => _x( 'Books', 'Post type general name', 'textdomain' ),
		'singular_name'         => _x( 'Book', 'Post type singular name', 'textdomain' ),
		'menu_name'             => _x( 'Books', 'Admin Menu text', 'textdomain' ),
		'name_admin_bar'        => _x( 'Book', 'Add New on Toolbar', 'textdomain' ),
		'add_new'               => __( 'Add New', 'textdomain' ),
		'add_new_item'          => __( 'Add New Book', 'textdomain' ),
		'new_item'              => __( 'New Book', 'textdomain' ),
		'edit_item'             => __( 'Edit Book', 'textdomain' ),
		'view_item'             => __( 'View Book', 'textdomain' ),
		'all_items'             => __( 'All Books', 'textdomain' ),
		'search_items'          => __( 'Search Books', 'textdomain' ),
		'parent_item_colon'     => __( 'Parent Books:', 'textdomain' ),
		'not_found'             => __( 'No books found.', 'textdomain' ),
		'not_found_in_trash'    => __( 'No books found in Trash.', 'textdomain' ),
		'featured_image'        => _x( 'Book Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'textdomain' ),
		'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'textdomain' ),
		'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'textdomain' ),
		'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'textdomain' ),
		'archives'              => _x( 'Book archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'textdomain' ),
		'insert_into_item'      => _x( 'Insert into book', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'textdomain' ),
		'uploaded_to_this_item' => _x( 'Uploaded to this book', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'textdomain' ),
		'filter_items_list'     => _x( 'Filter books list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'textdomain' ),
		'items_list_navigation' => _x( 'Books list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'textdomain' ),
		'items_list'            => _x( 'Books list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'textdomain' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'books' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
	);

	register_post_type( 'books', $args );
    	$labels = array(
		'name'              => _x( 'Genres', 'taxonomy general name', 'textdomain' ),
		'singular_name'     => _x( 'Genre', 'taxonomy singular name', 'textdomain' ),
		'search_items'      => __( 'Search Genres', 'textdomain' ),
		'all_items'         => __( 'All Genres', 'textdomain' ),
		'parent_item'       => __( 'Parent Genre', 'textdomain' ),
		'parent_item_colon' => __( 'Parent Genre:', 'textdomain' ),
		'edit_item'         => __( 'Edit Genre', 'textdomain' ),
		'update_item'       => __( 'Update Genre', 'textdomain' ),
		'add_new_item'      => __( 'Add New Genre', 'textdomain' ),
		'new_item_name'     => __( 'New Genre Name', 'textdomain' ),
		'menu_name'         => __( 'Genre', 'textdomain' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'genre' ),
	);

	register_taxonomy( 'genre', array( 'book' ), $args );

}
add_action('init', 'create_custom_post_type'
);
