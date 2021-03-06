<?php
/**
 * My function 
 */
class Custom_Menu_Top_Walker extends Walker_Nav_Menu {
    function start_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = ( $depth > 0  ? str_repeat( "\t", $depth ) : '' ); // code indent
		$display_depth = ( $depth + 1); // because it counts the first submenu as 0
		// var_dump($depth);

        $classes = array(
			( $depth == -1 ? '' : 'dropdown-menu' ),
        );
        $class_names = implode( ' ', $classes );
 
        // Build HTML for output.
        $output .= "\n" . $indent . '<ul class="' . $class_names . '">' . "\n";
    }
 
    function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        global $wp_query;
		$indent = ( $depth > 0 ? str_repeat( "\t", $depth ) : '' ); 
        $depth_classes = array(
			( $args->walker->has_children && $depth == 0 ? 'has-dropdown' : '' ),
			( $args->walker->has_children && $depth > 0 ? 'dropdown-submenu' : '' ),
			( $item->current || $item->current_item_ancestor || $item->current_item_parent  ? 'active' : '' ),
        );
        $depth_class_names = esc_attr( implode( ' ', $depth_classes ) );
 
        // Passed classes.
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		// $class_names = in_array("current_page_item",$item->classes) ? ' active' : '';
        $class_names = esc_attr( implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) ) );
 
        // Build HTML.
        $output .= $indent . '<li class="' . $depth_class_names . '">';
 
        // Link attributes.
        $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
        $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
        $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
		$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

		// link parent not click 
		// $attributes .= ' data-toggle="' . ( $args->walker->has_children ? 'dropdown' : '' ) . '" class="' . ( $args->walker->has_children ? 'dropdown-toggle' : '' ) . '"';
        $attributes .= '" class="' . ( $args->walker->has_children ? 'dropdown-toggle' : '' ) . '"';
 
        // Build HTML output and pass through the proper filter.
        $item_output = sprintf( '%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
            $args->before,
            $attributes,
            $args->link_before,
            apply_filters( 'the_title', $item->title, $item->ID ),
            $args->link_after,
            $args->after
        );
        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }
}

if( function_exists('acf_add_options_page') ) {
	acf_add_options_page(array(
		'page_title' 	=> 'Theme General Settings',
		'menu_title'	=> 'Cấu hình chung',
		'menu_slug' 	=> 'theme-general-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
}

function custom_menu_top( $name ) {
	if (($name) && wp_get_nav_menu_items($name)) {
		$menu_items = wp_get_nav_menu_items($name);
		foreach( $menu_items as $menu_item ) {
			if( $menu_item->menu_item_parent == 0 ) {
					
				$parent = $menu_item->ID;
					
				$menu_array = array();
				foreach( $menu_items as $submenu ) {
					if( $submenu->menu_item_parent == $parent ) {
						$bool = true;
						$menu_array[] = '<li><a href="' . $submenu->url . '">' . $submenu->title . '</a></li>' ."\n";
					}
				}
				if( $bool == true && count( $menu_array ) > 0 ) {
						
					$menu_list .= '<div class="col-xs-12 col-sm-6 col-md-2 footer-widget-link">' ."\n";
					$menu_list .= '<div class="footer-widget-title">
					<h5>' . $menu_item->title . '</h5></div>' ."\n";
						
					$menu_list .= '<div class="footer-widget-content"><ul class="list-unstyled">' ."\n";
					$menu_list .= implode( "\n", $menu_array );
					$menu_list .= '</ul></div></div>' ."\n";
						
				} else {
						
					$menu_list .= '<div class="col-xs-12 col-sm-6 col-md-2 footer-widget-link">' ."\n";
					$menu_list .= '<div class="footer-widget-title">';
					$menu_list .= '<h5>' . $menu_item->title . '</h5></div></div>' ."\n";
				}
			}
		}
	}
	else {
		$menu_list = '<!-- no menu defined -->';
	}
	echo $menu_list;
}

function custom_menu_bottom( $name ) {
	if (($name) && wp_get_nav_menu_items($name)) {
		$menu_items = wp_get_nav_menu_items($name);
		foreach( $menu_items as $menu_item ) {
			$menu_list .= '<li><a href="' . $menu_item->url . '">' . $menu_item->title . '</a></li>' ."\n";
		}
	}
	else {
		$menu_list = '<!-- no menu defined -->';
	}
	echo $menu_list;
}

function replace_admin_menu_icons_css() {
    ?>
    <style>
        #adminmenu #menu-posts-tin_tuc div.wp-menu-image::before,
		#adminmenu #menu-posts-san_pham div.wp-menu-image::before {
			content: '\f105';
		}
		#adminmenu #menu-posts.menu-icon-post, #adminmenu #menu-comments.menu-icon-comments{
			display: none;
		}

    </style>
    <?php
}

add_action( 'admin_head', 'replace_admin_menu_icons_css' );

function getPostViews($postID){
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return "0";
    }
    return $count.'';
}

function limitText( $content, $length ) {
    $drop = explode( ' ', $content );
    $total = 0;
    foreach ( $drop as $key => $drops ) {
        $total += strlen( $drops ) + 1;
        echo $drops . ' ';
        if ( $total > $length ) {
            echo '...';
            break;
        }
    }
}

// function to count views.
function setPostViews($postID) {
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}

function pagination_san_pham(){
	// Sét dk lọc, lấy bài viết theo category sản phẩm
	$tax_query = '';
	$category = get_queried_object();
	if($category->post_parent !== 0)
	$tax_query =  array(
		array(
			'taxonomy' => 'category',
			'field' => 'slug',
			'terms' => $category->post_name
		)
	);

	// Sắp xếp theo ...
	$s_order = array();
	if (isset($_GET['orderby']) && isset($_GET['order']) && $_GET['orderby'] == "title")
	{
		$s_order = array(
			'orderby' => 'title',
			'order' => $_GET['order']
		);
	}
	
	// Đoạn 3
	// if (isset($_GET['orderby']) && isset($_GET['order']) && $_GET['orderby'] == "gia_moi")
	// {
	// 	$s_order = array(
	// 		'meta_key' => 'gia_moi',
	// 		'orderby' => 'meta_value',
	// 		'order' => $_GET['order'],
	// 	);
	// }

	// Sét số lượng bài viết trên 1 page
	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

	// Thực hiện lấy
	$args =  array(
		'post_type' => 'san_pham',
		'posts_per_page' => 2,
		'paged' => $paged,
		'tax_query' => $tax_query,
		'order' => 'desc'
	);
	$args = array_merge($args, $s_order);
	$the_query = new WP_Query($args); 

	// Xuất ra html
	if ( $the_query->have_posts() ) : 
		echo '<div class="row show-product">';
			while ( $the_query->have_posts() ) : $the_query->the_post(); 
			?>
				<div class="col-xs-12 col-sm-6 col-md-4 product">
					<div class="product-img">
					<img src="<?php echo wp_get_attachment_url(get_post_thumbnail_id($post->ID)) ?>" alt="<?php the_title() ?>"/>
						<div class="product-hover">
							<div class="product-action">
							<a class="btn btn-primary" href="<?php echo get_field('thong_tin_san_pham', $post->ID)[0]['lien_ket'] ?>">Mua ngay</a>
							<a class="btn btn-primary" href="<?php echo get_post_permalink($post->ID) ?>">Xem chi tiết</a>
							</div>
						</div>
						<!-- .product-overlay end -->
					</div>
					<!-- .product-img end -->
					<div class="product-bio">
						<div class="prodcut-cat">
							<a href="javascript:void(0)"><?php echo get_field('thong_tin_san_pham', $post->ID)[0]['thuong_hieu'] ?></a>
						</div>
						<!-- .product-cat end -->
						<div class="prodcut-title">
							<h3>
								<a href="<?php echo get_post_permalink($post->ID) ?>"><?php the_title() ?></a>
							</h3>
						</div>
						<!-- .product-title end -->
						<div class="product-price">
							<span><?php echo get_field('thong_tin_san_pham', $post->ID)[0]['gia_moi'] ?></span>
						</div>
						<!-- .product-price end -->

					</div>
					<!-- .product-bio end -->
				</div>
			<?php
			endwhile;
				wp_reset_postdata();
		echo '</div>'; 
		echo '<div class="row"><div class="col-xs-12 col-sm-12 col-md-12">';
			echo paginate_links( array(
				// 'base'         => str_replace( 999999999, '%#%', ( get_pagenum_link( 999999999 ) ) ),
				'total'        => $the_query->max_num_pages,
				'current'      => max( 1, $paged ),
				'format'       => '?paged=%#%',
				'type'         => 'list',
				// 'end_size'     => 2,
				// 'mid_size'     => 1,
				'prev_next'    => true,
				'prev_text'    => sprintf( '<i>%1$s</i>', __( '<i class="fa fa-angle-left"></i>', 'text-domain' ) ),
				'next_text'    => sprintf( '<i>%1$s</i>', __( '<i class="fa fa-angle-right"></i>', 'text-domain' ) ),
			) );
		echo '</div></div>'; 
	else : 
		echo '<p>Không tìm thấy sản phẩm nào</p>';
	endif;

	
}

add_action( 'pre_get_posts', 'se39294_search_pre_get_posts' );

function se39294_search_pre_get_posts( $query ) {
    if ( $query->is_main_query() && is_search() ) {
		$query->set( 'post_type', array('san_pham','tin_tuc') );
		$query->set( 'posts_per_page', 8);
    }
}

function bcdonline_breadcrumbs() {
	$delimiter = '';
	$name = 'Trang chủ'; //text for the 'Home' link
	$currentBefore = '<li class="active">';
	$currentAfter = '</li>';
	// echo '<span class="tip">.:BCDOnline:</span>';
  
	global $post;
	$home = get_bloginfo('url');
	
	if(is_home() && get_query_var('paged') == 0) 
		echo '<li class="home">' . $name . '</li>';
	else
		echo '<a class="home" href="' . $home . '">' . $name . '</a> '. $delimiter . ' / ';
  
	if ( is_category() ) {
		global $wp_query;
		$cat_obj = $wp_query->get_queried_object();
		$thisCat = $cat_obj->term_id;
		$thisCat = get_category($thisCat);
		$parentCat = get_category($thisCat->parent);
		if ($thisCat->parent != 0) echo(get_category_parents($parentCat, TRUE, ' ' . $delimiter . ' '));
		echo $currentBefore;
		single_cat_title();
		echo $currentAfter;
  
	} elseif ( is_single() ) {
	  $cat = get_the_category(); $cat = $cat[0];
	  echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
	  echo $currentBefore;
	  the_title();
	  echo $currentAfter;
  
	} elseif ( is_page() && !$post->post_parent ) {
	  echo $currentBefore;
	  the_title();
	  echo $currentAfter;
  
	} elseif ( is_page() && $post->post_parent ) {
	  $parent_id  = $post->post_parent;
	  $breadcrumbs = array();
	  while ($parent_id) {
		$page = get_page($parent_id);
		$breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
		$parent_id  = $page->post_parent;
	  }
	  $breadcrumbs = array_reverse($breadcrumbs);
	  foreach ($breadcrumbs as $crumb) echo $crumb . ' ' . $delimiter . ' ';
	  echo $currentBefore;
	  the_title();
	  echo $currentAfter;
  
	} elseif ( is_search() ) {
	  echo $currentBefore . 'Search for ' . get_search_query() . $currentAfter;
  
	} elseif ( is_tag() ) {
	  echo $currentBefore;
	  single_tag_title();
	  echo $currentAfter;
  
	} elseif ( is_author() ) {
	   global $author;
	  $userdata = get_userdata($author);
	  echo $currentBefore. $userdata->display_name . $currentAfter;
  
	} elseif ( is_404() ) {
	  echo $currentBefore . 'Error 404' . $currentAfter;
	}
  
	if ( get_query_var('paged') )
	  echo $currentBefore . __('Page') . ' ' . get_query_var('paged') . $currentAfter;
}

// function search_by_title_only( $search, &$wp_query ) {
//     global $wpdb;
//     if ( empty( $search ) )
//         return $search; // skip processing - no search term in query
//     $q = $wp_query->query_vars;
//     $n = ! empty( $q['exact'] ) ? '' : '%';
//     $search =
//     $searchand = '';
//     foreach ( (array) $q['search_terms'] as $term ) {
//         $term = esc_sql( like_escape( $term ) );
//         $search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
//         $searchand = ' AND ';
//     }
//     if ( ! empty( $search ) ) {
//         $search = " AND ({$search}) ";
//         if ( ! is_user_logged_in() )
//             $search .= " AND ($wpdb->posts.post_password = '') ";
//     }
//     return $search;
// }
// add_filter( 'posts_search', 'search_by_title_only' );
/**
 * Twenty Seventeen functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 */

/**
 * Twenty Seventeen only works in WordPress 4.7 or later.
 */
if ( version_compare( $GLOBALS['wp_version'], '4.7-alpha', '<' ) ) {
	require get_template_directory() . '/inc/back-compat.php';
	return;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function twentyseventeen_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed at WordPress.org. See: https://translate.wordpress.org/projects/wp-themes/twentyseventeen
	 * If you're building a theme based on Twenty Seventeen, use a find and replace
	 * to change 'twentyseventeen' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'twentyseventeen' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	add_image_size( 'twentyseventeen-featured-image', 2000, 1200, true );

	add_image_size( 'twentyseventeen-thumbnail-avatar', 100, 100, true );

	// Set the default content width.
	$GLOBALS['content_width'] = 525;

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'top'    => __( 'Top Menu', 'twentyseventeen' ),
		'social' => __( 'Social Links Menu', 'twentyseventeen' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 *
	 * See: https://codex.wordpress.org/Post_Formats
	 */
	add_theme_support( 'post-formats', array(
		'aside',
		'image',
		'video',
		'quote',
		'link',
		'gallery',
		'audio',
	) );

	// Add theme support for Custom Logo.
	add_theme_support( 'custom-logo', array(
		'width'       => 250,
		'height'      => 250,
		'flex-width'  => true,
	) );

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/*
	 * This theme styles the visual editor to resemble the theme style,
	 * specifically font, colors, and column width.
 	 */
	add_editor_style( array( 'assets/css/editor-style.css', twentyseventeen_fonts_url() ) );

	// Define and register starter content to showcase the theme on new sites.
	$starter_content = array(
		'widgets' => array(
			// Place three core-defined widgets in the sidebar area.
			'sidebar-1' => array(
				'text_business_info',
				'search',
				'text_about',
			),

			// Add the core-defined business info widget to the footer 1 area.
			'sidebar-2' => array(
				'text_business_info',
			),

			// Put two core-defined widgets in the footer 2 area.
			'sidebar-3' => array(
				'text_about',
				'search',
			),
		),

		// Specify the core-defined pages to create and add custom thumbnails to some of them.
		'posts' => array(
			'home',
			'about' => array(
				'thumbnail' => '{{image-sandwich}}',
			),
			'contact' => array(
				'thumbnail' => '{{image-espresso}}',
			),
			'blog' => array(
				'thumbnail' => '{{image-coffee}}',
			),
			'homepage-section' => array(
				'thumbnail' => '{{image-espresso}}',
			),
		),

		// Create the custom image attachments used as post thumbnails for pages.
		'attachments' => array(
			'image-espresso' => array(
				'post_title' => _x( 'Espresso', 'Theme starter content', 'twentyseventeen' ),
				'file' => 'assets/images/espresso.jpg', // URL relative to the template directory.
			),
			'image-sandwich' => array(
				'post_title' => _x( 'Sandwich', 'Theme starter content', 'twentyseventeen' ),
				'file' => 'assets/images/sandwich.jpg',
			),
			'image-coffee' => array(
				'post_title' => _x( 'Coffee', 'Theme starter content', 'twentyseventeen' ),
				'file' => 'assets/images/coffee.jpg',
			),
		),

		// Default to a static front page and assign the front and posts pages.
		'options' => array(
			'show_on_front' => 'page',
			'page_on_front' => '{{home}}',
			'page_for_posts' => '{{blog}}',
		),

		// Set the front page section theme mods to the IDs of the core-registered pages.
		'theme_mods' => array(
			'panel_1' => '{{homepage-section}}',
			'panel_2' => '{{about}}',
			'panel_3' => '{{blog}}',
			'panel_4' => '{{contact}}',
		),

		// Set up nav menus for each of the two areas registered in the theme.
		'nav_menus' => array(
			// Assign a menu to the "top" location.
			'top' => array(
				'name' => __( 'Top Menu', 'twentyseventeen' ),
				'items' => array(
					'link_home', // Note that the core "home" page is actually a link in case a static front page is not used.
					'page_about',
					'page_blog',
					'page_contact',
				),
			),

			// Assign a menu to the "social" location.
			'social' => array(
				'name' => __( 'Social Links Menu', 'twentyseventeen' ),
				'items' => array(
					'link_yelp',
					'link_facebook',
					'link_twitter',
					'link_instagram',
					'link_email',
				),
			),
		),
	);

	/**
	 * Filters Twenty Seventeen array of starter content.
	 *
	 * @since Twenty Seventeen 1.1
	 *
	 * @param array $starter_content Array of starter content.
	 */
	$starter_content = apply_filters( 'twentyseventeen_starter_content', $starter_content );

	add_theme_support( 'starter-content', $starter_content );
}
add_action( 'after_setup_theme', 'twentyseventeen_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function twentyseventeen_content_width() {

	$content_width = $GLOBALS['content_width'];

	// Get layout.
	$page_layout = get_theme_mod( 'page_layout' );

	// Check if layout is one column.
	if ( 'one-column' === $page_layout ) {
		if ( twentyseventeen_is_frontpage() ) {
			$content_width = 644;
		} elseif ( is_page() ) {
			$content_width = 740;
		}
	}

	// Check if is single post and there is no sidebar.
	if ( is_single() && ! is_active_sidebar( 'sidebar-1' ) ) {
		$content_width = 740;
	}

	/**
	 * Filter Twenty Seventeen content width of the theme.
	 *
	 * @since Twenty Seventeen 1.0
	 *
	 * @param int $content_width Content width in pixels.
	 */
	$GLOBALS['content_width'] = apply_filters( 'twentyseventeen_content_width', $content_width );
}
add_action( 'template_redirect', 'twentyseventeen_content_width', 0 );

/**
 * Register custom fonts.
 */
function twentyseventeen_fonts_url() {
	$fonts_url = '';

	/*
	 * Translators: If there are characters in your language that are not
	 * supported by Libre Franklin, translate this to 'off'. Do not translate
	 * into your own language.
	 */
	$libre_franklin = _x( 'on', 'Libre Franklin font: on or off', 'twentyseventeen' );

	if ( 'off' !== $libre_franklin ) {
		$font_families = array();

		$font_families[] = 'Libre Franklin:300,300i,400,400i,600,600i,800,800i';

		$query_args = array(
			'family' => urlencode( implode( '|', $font_families ) ),
			'subset' => urlencode( 'latin,latin-ext' ),
		);

		$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
	}

	return esc_url_raw( $fonts_url );
}

/**
 * Add preconnect for Google Fonts.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param array  $urls           URLs to print for resource hints.
 * @param string $relation_type  The relation type the URLs are printed.
 * @return array $urls           URLs to print for resource hints.
 */
function twentyseventeen_resource_hints( $urls, $relation_type ) {
	if ( wp_style_is( 'twentyseventeen-fonts', 'queue' ) && 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'twentyseventeen_resource_hints', 10, 2 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function twentyseventeen_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Blog Sidebar', 'twentyseventeen' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Add widgets here to appear in your sidebar on blog posts and archive pages.', 'twentyseventeen' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer 1', 'twentyseventeen' ),
		'id'            => 'sidebar-2',
		'description'   => __( 'Add widgets here to appear in your footer.', 'twentyseventeen' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer 2', 'twentyseventeen' ),
		'id'            => 'sidebar-3',
		'description'   => __( 'Add widgets here to appear in your footer.', 'twentyseventeen' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'twentyseventeen_widgets_init' );

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with ... and
 * a 'Continue reading' link.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param string $link Link to single post/page.
 * @return string 'Continue reading' link prepended with an ellipsis.
 */
function twentyseventeen_excerpt_more( $link ) {
	if ( is_admin() ) {
		return $link;
	}

	$link = sprintf( '<p class="link-more"><a href="%1$s" class="more-link">%2$s</a></p>',
		esc_url( get_permalink( get_the_ID() ) ),
		/* translators: %s: Name of current post */
		sprintf( __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'twentyseventeen' ), get_the_title( get_the_ID() ) )
	);
	return ' &hellip; ' . $link;
}
add_filter( 'excerpt_more', 'twentyseventeen_excerpt_more' );

/**
 * Handles JavaScript detection.
 *
 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
 *
 * @since Twenty Seventeen 1.0
 */
function twentyseventeen_javascript_detection() {
	echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
}
add_action( 'wp_head', 'twentyseventeen_javascript_detection', 0 );

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function twentyseventeen_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", get_bloginfo( 'pingback_url' ) );
	}
}
add_action( 'wp_head', 'twentyseventeen_pingback_header' );

/**
 * Display custom color CSS.
 */
function twentyseventeen_colors_css_wrap() {
	if ( 'custom' !== get_theme_mod( 'colorscheme' ) && ! is_customize_preview() ) {
		return;
	}

	require_once( get_parent_theme_file_path( '/inc/color-patterns.php' ) );
	$hue = absint( get_theme_mod( 'colorscheme_hue', 250 ) );
?>
	<style type="text/css" id="custom-theme-colors" <?php if ( is_customize_preview() ) { echo 'data-hue="' . $hue . '"'; } ?>>
		<?php echo twentyseventeen_custom_colors_css(); ?>
	</style>
<?php }
add_action( 'wp_head', 'twentyseventeen_colors_css_wrap' );

/**
 * Enqueue scripts and styles.
 */
function twentyseventeen_scripts() {
	// Add custom fonts, used in the main stylesheet.
	wp_enqueue_style( 'twentyseventeen-fonts', twentyseventeen_fonts_url(), array(), null );

	// Theme stylesheet.
	wp_enqueue_style( 'twentyseventeen-style', get_stylesheet_uri() );

	// Load the dark colorscheme.
	if ( 'dark' === get_theme_mod( 'colorscheme', 'light' ) || is_customize_preview() ) {
		wp_enqueue_style( 'twentyseventeen-colors-dark', get_theme_file_uri( '/assets/css/colors-dark.css' ), array( 'twentyseventeen-style' ), '1.0' );
	}

	// Load the Internet Explorer 9 specific stylesheet, to fix display issues in the Customizer.
	if ( is_customize_preview() ) {
		wp_enqueue_style( 'twentyseventeen-ie9', get_theme_file_uri( '/assets/css/ie9.css' ), array( 'twentyseventeen-style' ), '1.0' );
		wp_style_add_data( 'twentyseventeen-ie9', 'conditional', 'IE 9' );
	}

	// Load the Internet Explorer 8 specific stylesheet.
	wp_enqueue_style( 'twentyseventeen-ie8', get_theme_file_uri( '/assets/css/ie8.css' ), array( 'twentyseventeen-style' ), '1.0' );
	wp_style_add_data( 'twentyseventeen-ie8', 'conditional', 'lt IE 9' );

	// Load the html5 shiv.
	wp_enqueue_script( 'html5', get_theme_file_uri( '/assets/js/html5.js' ), array(), '3.7.3' );
	wp_script_add_data( 'html5', 'conditional', 'lt IE 9' );

	wp_enqueue_script( 'twentyseventeen-skip-link-focus-fix', get_theme_file_uri( '/assets/js/skip-link-focus-fix.js' ), array(), '1.0', true );

	$twentyseventeen_l10n = array(
		'quote'          => twentyseventeen_get_svg( array( 'icon' => 'quote-right' ) ),
	);

	if ( has_nav_menu( 'top' ) ) {
		wp_enqueue_script( 'twentyseventeen-navigation', get_theme_file_uri( '/assets/js/navigation.js' ), array( 'jquery' ), '1.0', true );
		$twentyseventeen_l10n['expand']         = __( 'Expand child menu', 'twentyseventeen' );
		$twentyseventeen_l10n['collapse']       = __( 'Collapse child menu', 'twentyseventeen' );
		$twentyseventeen_l10n['icon']           = twentyseventeen_get_svg( array( 'icon' => 'angle-down', 'fallback' => true ) );
	}

	wp_enqueue_script( 'twentyseventeen-global', get_theme_file_uri( '/assets/js/global.js' ), array( 'jquery' ), '1.0', true );

	wp_enqueue_script( 'jquery-scrollto', get_theme_file_uri( '/assets/js/jquery.scrollTo.js' ), array( 'jquery' ), '2.1.2', true );

	wp_localize_script( 'twentyseventeen-skip-link-focus-fix', 'twentyseventeenScreenReaderText', $twentyseventeen_l10n );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'twentyseventeen_scripts' );

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for content images.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param string $sizes A source size value for use in a 'sizes' attribute.
 * @param array  $size  Image size. Accepts an array of width and height
 *                      values in pixels (in that order).
 * @return string A source size value for use in a content image 'sizes' attribute.
 */
function twentyseventeen_content_image_sizes_attr( $sizes, $size ) {
	$width = $size[0];

	if ( 740 <= $width ) {
		$sizes = '(max-width: 706px) 89vw, (max-width: 767px) 82vw, 740px';
	}

	if ( is_active_sidebar( 'sidebar-1' ) || is_archive() || is_search() || is_home() || is_page() ) {
		if ( ! ( is_page() && 'one-column' === get_theme_mod( 'page_options' ) ) && 767 <= $width ) {
			 $sizes = '(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px';
		}
	}

	return $sizes;
}
add_filter( 'wp_calculate_image_sizes', 'twentyseventeen_content_image_sizes_attr', 10, 2 );

/**
 * Filter the `sizes` value in the header image markup.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param string $html   The HTML image tag markup being filtered.
 * @param object $header The custom header object returned by 'get_custom_header()'.
 * @param array  $attr   Array of the attributes for the image tag.
 * @return string The filtered header image HTML.
 */
function twentyseventeen_header_image_tag( $html, $header, $attr ) {
	if ( isset( $attr['sizes'] ) ) {
		$html = str_replace( $attr['sizes'], '100vw', $html );
	}
	return $html;
}
add_filter( 'get_header_image_tag', 'twentyseventeen_header_image_tag', 10, 3 );

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for post thumbnails.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param array $attr       Attributes for the image markup.
 * @param int   $attachment Image attachment ID.
 * @param array $size       Registered image size or flat array of height and width dimensions.
 * @return array The filtered attributes for the image markup.
 */
function twentyseventeen_post_thumbnail_sizes_attr( $attr, $attachment, $size ) {
	if ( is_archive() || is_search() || is_home() ) {
		$attr['sizes'] = '(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px';
	} else {
		$attr['sizes'] = '100vw';
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'twentyseventeen_post_thumbnail_sizes_attr', 10, 3 );

/**
 * Use front-page.php when Front page displays is set to a static page.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param string $template front-page.php.
 *
 * @return string The template to be used: blank if is_home() is true (defaults to index.php), else $template.
 */
function twentyseventeen_front_page_template( $template ) {
	return is_home() ? '' : $template;
}
add_filter( 'frontpage_template',  'twentyseventeen_front_page_template' );

/**
 * Modifies tag cloud widget arguments to display all tags in the same font size
 * and use list format for better accessibility.
 *
 * @since Twenty Seventeen 1.4
 *
 * @param array $args Arguments for tag cloud widget.
 * @return array The filtered arguments for tag cloud widget.
 */
function twentyseventeen_widget_tag_cloud_args( $args ) {
	$args['largest']  = 1;
	$args['smallest'] = 1;
	$args['unit']     = 'em';
	$args['format']   = 'list';

	return $args;
}
add_filter( 'widget_tag_cloud_args', 'twentyseventeen_widget_tag_cloud_args' );

/**
 * Implement the Custom Header feature.
 */
require get_parent_theme_file_path( '/inc/custom-header.php' );

/**
 * Custom template tags for this theme.
 */
require get_parent_theme_file_path( '/inc/template-tags.php' );

/**
 * Additional features to allow styling of the templates.
 */
require get_parent_theme_file_path( '/inc/template-functions.php' );

/**
 * Customizer additions.
 */
require get_parent_theme_file_path( '/inc/customizer.php' );

/**
 * SVG icons functions and filters.
 */
require get_parent_theme_file_path( '/inc/icon-functions.php' );
