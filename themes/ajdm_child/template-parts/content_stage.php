<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ajdm
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php
		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );
		else :
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		endif;

		if ( 'post' === get_post_type() ) :
			?>
		<?php endif; ?>
	</header><!-- .entry-header -->

	<?php ajdm_post_thumbnail(); ?>

	<div class="entry-content">
		<?php
		the_content( sprintf(
			wp_kses(
				/* translators: %s: Name of current post. Only visible to screen readers */
				__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'ajdm' ),
				array(
					'span' => array(
						'class' => array(),
					),
				)
			),
			get_the_title()
		) );
		//Ajout des champs de ACF au stage individuel
		?>
        <p>Superviseur : <?php the_field('superviseur'); ?></p>
        <p>Languages requient : <?php the_field('langages'); ?></p>
        <p>Système d'exploitation : <?php the_field('type'); ?></p>
        <p>Adress : <?php the_field('adresse'); ?></p>
        <?php
		wp_link_pages( array(
			'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'ajdm' ),
			'after'  => '</div>',
		) );?>
	</div><!-- .entry-content -->
	<div class="entry-meta">
				<?php
				ajdm_posted_on();
				ajdm_posted_by();
				?>
			</div><!-- .entry-meta -->
	<footer class="entry-footer">
		<?php ajdm_entry_footer(); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->