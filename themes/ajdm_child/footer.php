<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package ajdm
 */


?>



	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
        <div class="wii"> 
            <div class="divFoot">
                <?php if( dynamic_sidebar('footer') ) :else : endif ?>
            </div>
            <div class="divFoot"> 
                <?php if( dynamic_sidebar('footer2') ) :else : endif ?>
            </div> 
        </div>
        <div class="wii2"> 
            <div class="divFoot">
                <?php if( dynamic_sidebar('footer3') ) :else : endif ?>
            </div>
            <div class="divFoot">
                <a href="<?php echo esc_url( __( 'https://wordpress.org/', 'ajdm' ) ); ?>">
                    <?php
                    /* translators: %s: CMS name, i.e. WordPress. */
                    //printf( esc_html__( 'Proudly powered by %s', 'ajdm' ), 'WordPress' );
                    ?>
                </a>
                <!-- <span class="sep"> | </span> -->
                    <?php
                    /* translators: 1: Theme name, 2: Theme author. */
                    //printf( esc_html__( 'Theme: %1$s by %2$s.', 'ajdm' ), 'ajdm', '<a href="http://underscores.me/">Underscores.me</a>' );
                    ?>
                
                <?php if( dynamic_sidebar('footer4') ) :else : endif ?>        
                                
            </div><!-- .site-info --> <!-- remplacer par un nouveau widget -->
        </div>
        
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
