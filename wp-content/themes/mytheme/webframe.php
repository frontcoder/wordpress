<?php /*
Template Name: WebFrame
*/ ?>

<?php get_header(); ?>

<div id="container">
    <div id="content" role="main" style='width:1050px'>
        <iframe src='/<?php echo $post->post_title; ?>' style='width:100%; height:60em;'></iframe>
    </div><!-- #content -->
</div><!-- #container -->

<?php get_footer(); ?>