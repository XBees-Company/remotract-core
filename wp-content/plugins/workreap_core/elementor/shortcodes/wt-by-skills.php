<?php
/**
 * Shortcode
 *
 *
 * @package    Workreap
 * @subpackage Workreap/admin
 * @author     Amentotech <theamentotech@gmail.com>
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists('Workreap_By_Skills') ){
	class Workreap_By_Skills extends Widget_Base {

		/**
		 *
		 * @since    1.0.0
		 * @access   static
		 * @var      base
		 */
		public function get_name() {
			return 'wt_element_by_skills';
		}

		/**
		 *
		 * @since    1.0.0
		 * @access   static
		 * @var      title
		 */
		public function get_title() {
			return esc_html__( 'Jobs by skills', 'workreap_core' );
		}

		/**
		 *
		 * @since    1.0.0
		 * @access   public
		 * @var      icon
		 */
		public function get_icon() {
			return 'eicon-skill-bar';
		}

		/**
		 *
		 * @since    1.0.0
		 * @access   public
		 * @var      category of shortcode
		 */
		public function get_categories() {
			return [ 'workreap-elements' ];
		}

		/**
		 * Register category controls.
		 * @since    1.0.0
		 * @access   protected
		 */
		protected function register_controls() {
			$skills	= elementor_get_taxonomies('projects', 'skills');
			$skills	= !empty($skills) ? $skills : array();
			
			//Content
			$this->start_controls_section(
				'content_section',
				[
					'label' => esc_html__( 'Content', 'workreap_core' ),
					'tab' => Controls_Manager::TAB_CONTENT,
				]
			);
			
			$this->add_control(
				'section_heading',
				[
					'type'      	=> Controls_Manager::TEXT,
					'label'     	=> esc_html__( 'Heading', 'workreap_core' ),
					'description'   => esc_html__( 'Add section heading. Leave it empty to hide.', 'workreap_core' ),
				]
			);
			
			$this->add_control(
				'btn_title',
				[
					'type'      	=> Controls_Manager::TEXT,
					'label' 		=> esc_html__('Button Title', 'workreap_core'),
        			'description' 	=> esc_html__('Add button title. Leave it empty to hide button.', 'workreap_core'),
				]
			);
			
			$this->add_control(
				'btn_link',
				[
					'type'      	=> Controls_Manager::TEXT,
					'label' 		=> esc_html__('Button Link', 'workreap_core'),
        			'description' 	=> esc_html__('Add button link. Leave it empty to hide.', 'workreap_core'),
				]
			);
			
			$this->add_control(
				'skills',
				[
					'type'      	=> Controls_Manager::SELECT2,
					'label'			=> esc_html__('Skills', 'workreap_core'),
					'desc' 			=> esc_html__('Select skills to display.', 'workreap_core'),
					'options'   	=> $skills,
					'multiple' 		=> true,
					'label_block' 	=> true,
				]
			);

			$this->add_control(
				'version',
				[
					'type'      	=> Controls_Manager::SELECT,
					'label' 		=> esc_html__('Select Version', 'workreap_core'),
					'options' 		=> [
										'v_1' => 'Version 1',
										'v_2' => 'Version 2',
										],
					'default' 		=> 'v1',
				]
			);
			
			$this->add_control(
				'link_target',
				[
					'type'      	=> Controls_Manager::SELECT,
					'label' 		=> esc_html__('Link Target', 'workreap_core'),
					'desc'			=> esc_html__('Do you want to search freelancers or jobs?', 'workreap_core'),
					'options' 		=> [
										'jobs' => esc_html__('Jobs', 'workreap_core'),
										'freelancer' => esc_html__('Freelancer', 'workreap_core'),
										],
					'default' 		=> 'jobs',
				]
			);
			
			$this->end_controls_section();
		}

		/**
		 * Render shortcode
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function render() {
			$settings = $this->get_settings_for_display();

			$section_heading     	= !empty($settings['section_heading']) ? $settings['section_heading'] : '';
			$skills					= !empty($settings['skills']) ? $settings['skills'] : array();
			$view_title				= !empty( $settings['btn_title'] )  ? $settings['btn_title'] : '';
			$view_url				= !empty( $settings['btn_link'] )  ? $settings['btn_link'] : '';
			$version  	    		= !empty($settings['version']) ? $settings['version'] : 'v1';
			
			$search_page	= '';
			if( function_exists('workreap_get_search_page_uri') ){
				$link_target     = !empty($settings['link_target']) ? $settings['link_target'] : 'jobs';
				$search_page     = workreap_get_search_page_uri($link_target);
			}

			$class = '';
			if($version === 'v_2') {
				$class = 'wt-footeraboutus-two';
			}

			?>
			<div class="wt-sc-by-skills wt-haslayout <?php echo esc_attr($class); ?>">
				<?php if( !empty($skills) && apply_filters('workreap_check_plugin_activated','core') === true) {?>
					<div class="wt-widgetskills">
						<div class="wt-fwidgettitle">
							<h3><?php echo esc_html($section_heading);?></h3>
						</div>
						<ul class="wt-fwidgetcontent">
							<?php foreach( $skills as $skill ) { 
									$skill      = get_term($skill);
									$query_arg['skills[]'] 	= urlencode($skill->slug);
									$url                 		= add_query_arg( $query_arg, esc_url($search_page));
							?>
							<li><a href="<?php echo esc_url($url);?>"><?php echo esc_html($skill->name);?></a></li>
							<?php }?>
							<?php if( !empty($view_title) ) {?>
								<li class="wt-viewmore"><a href="<?php echo esc_url($view_url);?>"><?php echo esc_html($view_title);?></a></li>
							<?php } ?>
						</ul>
					</div>
				<?php } ?>
			</div>
		<?php 
		}

	}

	Plugin::instance()->widgets_manager->register( new Workreap_By_Skills ); 
}