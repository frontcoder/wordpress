<?php /*
Template Name: WebIndex
*/ ?>

<?php get_header(); ?>

    <div id="container">
        <div id="content" role="main">

            <?php
                $pageid = get_the_ID();
                $webs = get_pages(array('child_of' => $pageid));
                foreach ($webs as $key => $value) {
                    $name = $value->post_name;
                    $title = $value->post_title;
                    ?>
                        <div class='gameLink'>
                            <a href='<?php echo get_permalink($pageid).$name; ?>'>
                               <img src='<?php echo "/${title}/logo.png"; ?>'/><br/>
                               <b><?php echo $title; ?></b>
                            </a>
                        </div>
                    <?php
                }
            ?>

        </div><!-- #content -->

        <?php get_sidebar(); ?>

    </div><!-- #container -->

<?php get_footer(); ?>