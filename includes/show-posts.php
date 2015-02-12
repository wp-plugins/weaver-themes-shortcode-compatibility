<?php
/*
 Weaver II [weaver_show_posts]

*/

// same as Weaver II Pro shortcodes - except add_shortcode mapped to just add_shortcode here

function weaverii_show_posts_shortcode($args = '') {
    /* implement [weaver_show_posts]  */

/* DOC NOTES:
CSS styling: The group of posts will be wrapped with a <div> with a class called
.wvr-show-posts. You can add an additional class to that by providing a 'class=classname' option
(without the leading '.' used in the actual CSS definition). You can also provide inline styling
by providing a 'style=value' option where value is whatever styling you need, each terminated
with a semi-colon (;).

The optional header is in a <div> called .wvr_show_posts_header. You can add an additional class
name with 'header_class=classname'. You can provide inline styling with 'header_style=value'.

.wvr-show-posts .hentry {margin-top: 0px; margin-right: 0px; margin-bottom: 40px; margin-left: 0px;}
.widget-area .wvr-show-posts .hentry {margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px;}
*/

    global $more;
    global $weaverii_cur_post_id;

    extract(shortcode_atts(array(
	     /* query options */
	    'cats' => '',			/* by slug, use - to exclude  */
	    'tags' => '',			/* by slug (tag) */
	    'author' => '',			/* author - use nickname (auhor_name)*/
	    'author_id' => '',			/* list of author IDs */
	    'single_post' => '',		/* by slug - only one article (name) */
	    'post_type' => '',			/* add post_type */
	    'orderby' => 'date',		/* author | date | title | rand | modified | parent {date} (orderby) */
	    'sort' => 'DESC',			/* ASC | DESC {DESC} (order)*/
	    'number' => '5',			/* number of posts to show  {5} (posts_per_page)*/
        'paged' => false,			/* use paging? */
        'sticky' => false,          // show sticky?
        'nth' => '0',			/* show just the nth post that matches other criteria */
	    /* formatting options */
	    'show' => 'full',			/* show: title | title_featured | excerpt | full | titlelist  */
	    'hide_title' => '',			/* hide the title? */
	    'hide_top_info' => '',		/* hide the top info line */
	    'hide_bottom_info' => '',		/* hide bottom info line */
	    'show_featured_image' => '', 	/* force showing featured image */
	    'hide_featured_image' => '', 	/* force showing featured image */
	    'show_avatar' => '',		/* show the author avatar */
	    'show_bio' => '',			/* show the bio below */
	    'excerpt_length' => '',		/* override excerpt length */
	    'style' => '',			/* inline CSS style for wvr-show-posts */
	    'class' => '',			/* optional class to allow outside styling */
	    'header' => '',			/* optional header for post */
	    'header_style' => '',		/* styling for the header */
	    'header_class' => '',		/* class for header */
	    'more_msg' => '',			/* replacement for Continue Reading */
	    'left' => '',
	    'right' => '',
	    'clear' => ''
    ), $args));

    $save_cur_post = $weaverii_cur_post_id;

    /* Setup query arguments using the supplied args */
    $qargs = array(
        'ignore_sticky_posts' => 1
    );

    $qargs['orderby'] = $orderby;	/* enter opts that have defaults first */
    $qargs['order'] = $sort;
    $qargs['posts_per_page'] = $number;
    if (!empty($cats)) $qargs['cat'] = weaverii_cat_slugs_to_ids($cats);
    if (!empty($tags)) $qargs['tag'] = $tags;
    if (!empty($single_post)) $qargs['name'] = $single_post;
    if (!empty($author)) $qargs['author_name'] = $author;
    if (!empty($author_id)) $qargs['author'] = $author_id;
    if (!empty($post_type)) $qargs['post_type'] = $post_type;
    if (!empty($sticky) && $sticky) $qargs['ignore_sticky_posts'] = 0;

    weaverii_sc_reset_opts();

    weaverii_sc_setopt('showposts',true);	// global to see if we are in this function

    weaverii_sc_setopt('show',$show);	// this will always be set

    if ($hide_title != '') weaverii_sc_setopt('hide_title',true);
    if ($hide_top_info != '') weaverii_sc_setopt('hide_top_info',true);
    if ($hide_bottom_info != '') weaverii_sc_setopt('hide_bottom_info',true);
    if ($show_featured_image != '') weaverii_sc_setopt('show_featured_image',true);
    if ($hide_featured_image != '') weaverii_sc_setopt('hide_featured_image',true);
    if ( isset($args['show_avatar'])) {
        if ($show_avatar) {
            weaverii_sc_setopt('show_avatar', true);
        } else {
            weaverii_sc_setopt('show_avatar','no');
        }
    }

    if ($excerpt_length != '') weaverii_sc_setopt('excerpt_length',$excerpt_length);
    if ($more_msg != '') weaverii_sc_setopt('more_msg',$more_msg);

    if ( $paged ) {
	if ( get_query_var( 'paged' ) )
	    $qargs['paged'] = get_query_var('paged');
	else if ( get_query_var( 'page' ) )
	    $qargs['paged'] = get_query_var( 'page' );
	else
	    $qargs['paged'] = 1;
    }

    $ourposts = new WP_Query(apply_filters('weaver_show_posts_wp_query',$qargs, $args));
	// now modify the query using custom fields for this page

    /* now start the content */

    $div_add = '';
    if ($left) $class .= ' weaver-left';
    else if ($right) $class .= ' weaver-right';
    if (!empty($style)) $div_add = ' style="' . $style . '"';
    $content = '<div class="wvr-show-posts ' . $class . '"'  . $div_add . '>';

    $h_add = '';
    if (!empty($header_style)) $h_add = ' style="' . $header_style . '"';

    if (!empty($header)) {
        $content .= '<div class="wvr-show-posts-header ' . $header_class . '"' . $h_add . '>' . $header . '</div>';
    }

    ob_start();	// use built-in weaver code to generate a weaver standard post

    if ($show == 'titlelist') echo '<ul>';

    weaverii_post_count_clear();
    $posts_out = 0;

    if ($paged && $ourposts->have_posts()) {		// top paging?
        global $wp_query;
        $wp_query = $ourposts;
        weaverii_content_nav( 'nav-above' );
    }

    while ( $ourposts->have_posts() ) {
        $ourposts->the_post();
        weaverii_post_count_bump();
        $weaverii_cur_post_id = get_the_ID();
            $posts_out++;
        if ($nth != 0) {
            if ($posts_out < $nth)
                continue;
            if ($posts_out > $nth)
                break;			// all done...
        }

        // weaverii_per_post_style();
        if ($show == 'titlelist') {
    ?>
            <li><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr(__( 'Permalink to %s','weaver-ii')),
           the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></li>
    <?php
        } else {
            get_template_part( 'content', get_post_format() );
        }

    } // end loop
    if ($show == 'titlelist') echo "</ul>\n";
    if (!empty($show_bio) && get_the_author_meta( 'description' ) ) { ?>
    <hr />
		<div id="author-info">
			<div id="author-avatar">
				<?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'weaverii_author_bio_avatar_size', 68 ) ); ?>
			</div><!-- #author-avatar -->
			<div id="author-description">
				<h2><?php printf( esc_attr__( 'About %s','weaver-ii'), get_the_author() ); ?></h2>
				<?php the_author_meta( 'description' ); ?>
				<div id="author-link">
					<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author">
						<?php printf( __( 'View all posts by %s <span class="meta-nav">&rarr;</span>','weaver-ii'), get_the_author() ); ?>
					</a>
				</div><!-- #author-link	-->
			</div><!-- #author-description -->
		</div><!-- #entry-author-info -->
<?php
    }
    echo "<div class=\"weaver-clear\"></div>\n";
    if ($paged && $ourposts->have_posts()) {
        global $wp_query;
        $wp_query = $ourposts;
        weaverii_content_nav( 'nav-below' );
    }

    $content .= ob_get_clean();	// get the output

    // get posts

    $content .= '</div><!-- #wvr-show-posts -->';
    if ($clear) $content .= "<div class=\"weaver-clear\"></div>\n";
    wp_reset_query();
    wp_reset_postdata();

    $weaverii_cur_post_id = $save_cur_post;

    weaverii_sc_reset_opts();	// done, clear for other shortcodes

    return $content;
}

add_shortcode('weaver_show_posts', 'weaverii_show_posts_shortcode');

?>
