<?php
/**
 * The Widget Class
 *
 * @package     relatedYouTubeVideos
 * @copyright   Copyright (c) 2013 http://www.meomundo.com
 * @author      Christian Doerr <doerr@meomundo.com>
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 *
 */
class RelatedYouTubeVideos_Widget extends WP_Widget {

  /**
   * @var string $path ABsolute path to the plugin directory.
   */
  protected $path;

  /**
   * @var string $slug Plugin handler.
   */
  protected $slug;
  
  /**
   * @var object $API Object of the class RelatedYouTubeVideos_API.
   */
  protected $API;
  
  /**
   * The constructor.
   */
	public function __construct() {
  
    /**
     * Since this class in extending from WP_Widgets while the API is extending from Meomundo_WP
     * the "basic plugin configuration" has to be manually redone again.
     */
    $this->slug = 'relatedyoutubevideos';

    $this->path = realpath( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
    
    if( !class_exists( 'RelatedYouTubeVideos_API' ) ) {
      
      include_once $this->path . 'lib' . DIRECTORY_SEPARATOR . 'RelatedYouTubeVideos' . DIRECTORY_SEPARATOR . 'API.php';

    }
    
    /**
     * Use the WordPress Widget API.
     */
    parent::__construct(
      $this->slug,
			__( 'Related YouTube Videos', $this->slug ),
			array(
        'description' => __( 'Embeds YouTube videos that are related to the current post(s) in some way.', $this->slug ),
      )
		);
    
    $this->API = new RelatedYouTubeVideos_API();
		
	}

  /**
   * The Widget Backend.
   *
   * @param array $instance An array of the widget options that have been saved.
   */
 	public function form( $instance ) {

    $data         = $this->API->validateConfiguration( $instance );
    
    // Custom Defaults for Widgets
    if( $data['width'] == 0 ) {
      
      $data['width'] = 360;
      
    }

    if( $data['height'] == 0 ) {
      
      $data['height'] = 280;
      
    }

    $orderOptions = array( 'relevance', 'published', 'viewCount', 'rating' );

    $relPostTitle = ( strtolower( $data['relation'] ) == 'posttitle' )  ? ' checked="checked"' : '';

    $relPostTags  = ( strtolower( $data['relation'] ) == 'posttags' )  ? ' checked="checked"' : '';

    $relKeywords  = ( strtolower( $data['relation'] ) == 'keywords' )   ? ' checked="checked"' : '';
    
    $wpSearch     = ( $data['wpSearch'] == true ) ? ' checked="checked"' : '';
    
    $exact        = ( $data['exact'] == true ) ? ' checked="checked"' : '';

    /**
     * Generating the HTML form for the widget options.
     */
    $html         = '';

    // Widget Settings: Title
    $html .= '<h3>' . __( 'Widget', $this->slug ) . '</h3>' . "\n";
    $html .= '<ul>' . "\n";
    $html .= ' <li>' . "\n";
    $html .= '  <label for="' . $this->get_field_id( 'title' ) . '" style="display:inline-block;width:75px;text-align:right;">' . __( 'Title:', $this->slug ) . '</label>' . "\n";
    $html .= '  <input type="text" name="' . $this->get_field_name( 'title' ) . '" value="' . $data['title'] . '" />' . "\n";
    $html .= " </li>\n";
    $html .= "</ul>\n";

    // Related by postType, postTags, or keywords (in which case you have to enter your search terms/keywords)
    $html .= '<fieldset>' . "\n";
    $html .= ' <h3 style="margin-top:0;">' . __( 'Related By', $this->slug ) . '</h3>' . "\n";
    $html .= ' <ul>' . "\n";
    $html .= '  <li><input type="radio" name="' . $this->get_field_name( 'relation' ) . '" value="postTitle"' . $relPostTitle . ' /> <label>' . __( 'Post Title', $this->slug ) . '</label></li>' . "\n";
    $html .= '  <li><input type="radio" name="' . $this->get_field_name( 'relation' ) . '" value="postTags"' . $relPostTags . ' /> <label>' . __( 'Post Tags', $this->slug ) . '</label></li>' . "\n";
    $html .= '  <li><input type="radio" name="' . $this->get_field_name( 'relation' ) . '" value="keywords"' . $relKeywords . ' /> <label>' . __( 'Keywords: ', $this->slug ) . '</label><input type="text" name="' . $this->get_field_name( 'terms' ) . '" value="' . $data['terms'] . '" /></li>';
    $html .= '  <li><input type="checkbox" name="' . $this->get_field_name( 'exact' ) . '" ' . $exact . ' /> <label> ' . __( '(try) exact match', $this->slug ) . "</label></li>\n";
    $html .= ' </ul>' . "\n";
    $html .= ' <input type="checkbox" name="' . $this->get_field_name( 'wpSearch' ) . '" ' . $wpSearch . ' /> <label> ' . __( 'Site Search (On Search Results Page)', $this->slug ) . "</label>\n";
    $html .= '</fieldset>' . "\n";

    $html .= '<h3>' . __( 'Advanced Settings', $this->slug ) . '</h3>' . "\n";

    $html .= '<ul>' . "\n";

    // Order by  (default) relevance, published (date), viewCount, or ratings
    $html .= ' <li>' . "\n";
    $html .= '  <label for="' . $this->get_field_id( 'orderBy' ) . '" style="display:inline-block;width:75px;text-align:right;">' . __( 'Order By:', $this->slug ) . '</label>' . "\n";
    $html .= '  <select name="' . $this->get_field_name( 'orderBy' ) . '" size="1">' . "\n";

    foreach( $orderOptions as $orderOption ) {

      $html .= '   <option value="' . $orderOption . '"';
      
      if( strtolower( $orderOption ) == strtolower( $data['orderBy'] ) ) {

        $html .= ' selected="selected"';
        
      }
      
      $html .= '>' . $orderOption . '</option>' . "\n";

    }

    $html .= '  </select>' . "\n";
    $html .= ' </li>' . "\n";

    // Video object width
    $html .= ' <li>' . "\n";
    $html .= '  <label for="' . $this->get_field_id( 'width' ) . '" style="display:inline-block;width:75px;text-align:right;">' . __( 'Width:', $this->slug ) . '</label>' . "\n";
    $html .= '  <input type="text" name="' . $this->get_field_name( 'width' ) . '" value="' . $data['width'] . '" />' . "\n";
    $html .= ' </li>' . "\n";

    // Video object height
    $html .= ' <li>' . "\n";
    $html .= '  <label for="' . $this->get_field_id( 'height' ) . '" style="display:inline-block;width:75px;text-align:right;">' . __( 'Height:', $this->slug ) . '</label>' . "\n";
    $html .= '  <input type="text" name="' . $this->get_field_name( 'height' ) . '" value="' . $data['height'] . '" />' . "\n";
    $html .= ' </li>' . "\n";

    // Offset - skip this number of videos/search results
    $html .= ' <li>' . "\n";
    $html .= '  <label for="' . $this->get_field_id( 'start' ) . '" style="display:inline-block;width:75px;text-align:right;">' . __( 'Offset:', $this->slug ) . '</label>' . "\n";
    $html .= '  <input type="text" name="' . $this->get_field_name( 'start' ) . '" value="' . $data['start'] . '"/>' . "\n";
    $html .= ' </li>' . "\n";

    // Number of videos / search results that will be returned (between 1 and 10)
    $html .= ' <li>' . "\n";
    $html .= '  <label for="' . $this->get_field_id( 'max' ) . '" style="display:inline-block;width:75px;text-align:right;">' . __( 'Nr. of Videos:', $this->slug ) . '</label>' . "\n";
    $html .= '  <select name="' . $this->get_field_name( 'max' ) . '" size="1">' . "\n";

    for( $i = 1; $i <= 10; $i++ ) {

      $html .= '  <option value="' . $i . '"';
      
      if( $i == $data['max'] ) {
        
        $html .= ' selected="selected"';
        
      }
      
      $html .= '>' . $i . '</option>' . "\n";

    }

    $html .= '  </select>' . "\n";
    $html .= ' </li>' . "\n";

    $html .= '</ul>' . "\n";

    echo $html;

	}

  /**
   * Validating and (preparing the) saving of the widget options.
   *
   * @param   array   $newInstance An array containing all the newly entered values/options.
   * @param   array   $oldInstance An array containing all the previously saved values/options.
   * @return  array   Validated and normalized array that then will be saved through the WordPress widget API.
   */
	public function update( $newInstance, $oldInstance ) {

    return $this->API->validateConfiguration( $newInstance );

	}

  /**
   * The widget frontend output.
   *
   * @param   array   $args WordPress widget specify configuration.
   * @param   array   $instance The backend options for this widget instance.
   */
	public function widget( $args, $instance ) {

    $beforeWidget = isset( $args['before_widget'] ) ? strip_tags( trim( $args['before_widget'] ) ) : '';

    $afterWidget  = isset( $args['after_widet'] )   ? strip_tags( trim( $args['after_widget'] ) ) : '';

    $beforeTitle  = isset( $args['before_title'] )  ? strip_tags( trim( $args['before_title'] ) ) : '';

    $afterTitle   = isset( $args['after_title'] )   ? strip_tags( trim( $args['after_title'] ) )  : '';
    
    $data         = $this->API->validateConfiguration( $instance );

    // Custom Defaults for the shortcode
    if( $data['width'] == 0 ) {
      
      $data['width'] = 720;
      
    }

    if( $data['height'] == 0 ) {
      
      $data['height'] = 480;
      
    }

    
    $wpSearch     = trim( get_search_query() );

    $searchTerms  = ( $data['wpSearch'] == true && $wpSearch !== '' ) ? $wpSearch : $data['search'];
    
    $results      = $this->API->searchYouTube(
      array(
        'searchTerms' => $searchTerms,
        'orderBy'     => $data['orderBy'],
        'start'       => $data['start'],
        'max'         => $data['max'],
        'apiVersion'  => $data['apiVersion'],
        'exact'       => $data['exact']
      )
    );
    
    /**
     * View results in form of an unordered HTML list.
     */
    $html         = '';
    
    $html         .= $beforeWidget;
    
    if( $data['title'] !== '' ) {
      
      $html .= $beforeTitle . $data['title'] . $afterTitle;
      
    }

    $html .= $this->API->displayResults(
      $results,
      array(
        'id'      => 'relatedVideos',
        'width'   => $data['width'],
        'height'  => $data['height']
      )
    );

    $html .= $afterWidget;
    
    echo $html;

	}

}
?>