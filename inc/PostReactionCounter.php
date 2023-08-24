<?php
namespace PostReaction;

class PostReactionCounter{

    private $table_name = 'post_reactions';
    private $allowed_reactions = array('like', 'love', 'wow', 'angry');
    private $settings = array();
    public function register() {

        $this->settings =  $this->getSettings();
        // Register hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_update_post_reaction', array($this, 'save_reaction_callback'));
        add_action('wp_ajax_nopriv_update_post_reaction', array($this, 'save_reaction_callback'));
        add_action('the_content', array($this, 'display_reactions_count'));
        add_action('wp_footer', [$this, 'footerAlert']);
    }

    // Enqueue frontend scripts and styles
    public function enqueue_scripts() {
        wp_enqueue_style('post-reactions', PRC_PLUGIN_DIR . 'dist/public.css', array(), PRC_VER, 'all');

        wp_enqueue_script('post-reactions-script', PRC_PLUGIN_DIR . 'dist/public.js', array('jquery'), PRC_VER, true);
        wp_localize_script( 'post-reactions-script', 'postReactScript', [
            'ajaxURL' => admin_url('admin-ajax.php'),
            'onlyUserCanReact' => $this->settings['onlyUserCanReact']
        ] );
    }

    // AJAX callback to save reaction
    public function save_reaction_callback() {
        if(get_current_user_id() == 0 && $this->settings['onlyUserCanReact']) {
            wp_send_json_error('Please Login to react!');
            
        }

        if(is_array($this->settings['customReacts'] ?? [])){
            foreach($this->settings['customReacts'] as $react){
                if(isset($react['id'])){
                    $this->allowed_reactions = array_merge($this->allowed_reactions, [$react['id']]);
                }
            }
        }

        // Example: 
        $post_id = absint(sanitize_text_field($_POST['post_id']));
        $reaction_type = sanitize_text_field($_POST['reaction_type']);

        
        // Save the reaction in the custom table
        if (!in_array($reaction_type, $this->allowed_reactions)) {
            wp_send_json_error('Invalid reaction type.');
        }
        
        // Check if the reaction already exists for the post
        if ($this->reaction_exists($post_id, $reaction_type)) {
            if($this->remove_reaction($post_id, $reaction_type)){
                wp_send_json_success(['count' => $this->get_reactions_count($post_id)]);
            }else {
                wp_send_json_error('Reaction already exists.');
            }
        }
        
        // Save the reaction in the custom table
        if ($this->save_reaction($post_id, $reaction_type)) {
            // wp_send_json_error( 'trying to save' );
            wp_send_json_success(['count' => $this->get_reactions_count($post_id)]);
        } else {
            wp_send_json_error('Failed to save reaction.');
        }

        // Send a response back (if needed)
        wp_send_json_success(['count' => $this->get_reactions_count($post_id)]);
    }

    // Display reactions count on posts
    public function display_reactions_count($content) {
        global $post;
        if(!$this->settings['enabled'] || !in_array($post->post_type, $this->settings['postTypes'])) {
            return $content;
        }
        
        $reactions_count = $this->settings['beforeContent'] . $this->get_reactions_contents($post->ID). $this->settings['afterContent'];

        // Format and add the count to the content
        $count_html = sprintf('<div class="post-reactions-count">%s</div>', $reactions_count);

        if($this->settings['contentPosition'] === 'after_content') {
            return  $content . $count_html;
        }
        return $count_html . $content;
    }

    private function get_reactions_contents($post_id){
       
        $formatted_counts = $this->get_reactions_count($post_id);
        $reacted = $formatted_counts['reacted'];

        $likeClass = $reacted === 'like' ? 'reacted_to': '';
        $loveClass = $reacted === 'love' ? 'reacted_to': '';
        $wowClass = $reacted === 'wow' ? 'reacted_to': '';
        $angryClass = $reacted === 'angry' ? 'reacted_to': '';

        $count_html = '<ul class="post-reactions-list '. $this->settings['design'] . ' ">';

        if(in_array('like', $this->settings['enabledReacts'])){
            $count_html .= '<li title="like" class="'.$likeClass.'" data-post-id="'.$post_id.'" data-reaction-type="like"><svg fill="#000000" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52" enable-background="new 0 0 52 52" xml:space="preserve"> <g> <path d="M10.5,21h-5C4.7,21,4,21.7,4,22.5v23C4,46.3,4.7,47,5.5,47H8c2.2,0,4-1.8,4-4V22.5C12,21.7,11.3,21,10.5,21 z"/> <path d="M44,22h-6c-2.2,0-4-1.8-4-4V8c0-2.2-1.8-4-4-4h-2.5C26.7,4,26,4.7,26,5.5v6c0,5.3-3.7,10.5-8.5,10.5 c-0.8,0-1.5,0.7-1.5,1.5v20c0,0.8,0.6,1.5,1.4,1.5c6.8,0.3,9.1,3,16.2,3c7.5,0,14.4-0.8,14.4-9.5v-5V26C48,23.8,46.2,22,44,22z"/> </g> </svg> <span>' . $formatted_counts['like'] . '</span></li>';
        }

        if(in_array('love', $this->settings['enabledReacts'])){
            $count_html .= '<li title="love" class="'.$loveClass.'" data-post-id="'.$post_id.'" data-reaction-type="love"><svg viewBox="0 0 24 24" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/">
            <g transform="translate(0 -1028.4)">
            <path d="m7 1031.4c-1.5355 0-3.0784 0.5-4.25 1.7-2.3431 2.4-2.2788 6.1 0 8.5l9.25 9.8 9.25-9.8c2.279-2.4 2.343-6.1 0-8.5-2.343-2.3-6.157-2.3-8.5 0l-0.75 0.8-0.75-0.8c-1.172-1.2-2.7145-1.7-4.25-1.7z" fill="#e74c3c"/>
            </g>
             </svg> <span>' . $formatted_counts['love'] . '</span></li>';
        }

        if(in_array('wow', $this->settings['enabledReacts'])){
            $count_html .= '<li class="'.$wowClass.'" title="wow"  data-post-id="'.$post_id.'" data-reaction-type="wow"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="9.5" stroke="#222222" stroke-linecap="round"/>
            <circle cx="9" cy="9" r="1" fill="#222222" stroke="#222222" stroke-linecap="round"/>
            <circle cx="15" cy="9" r="1" fill="#222222" stroke="#222222" stroke-linecap="round"/>
            <path d="M15 15.5C15 16.8807 13.6569 18 12 18C10.3431 18 9 16.8807 9 15.5C9 14.1193 10.3431 13 12 13C13.6569 13 15 14.1193 15 15.5Z" fill="#222222"/>
            </svg> <span>' . $formatted_counts['wow'] . '</span></li>';
        }

        if(in_array('angry', $this->settings['enabledReacts'])){
            $count_html .= '<li class="'.$angryClass.'" title="angry"  data-post-id="'.$post_id.'" data-reaction-type="angry"><svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--fxemoji" preserveAspectRatio="xMidYMid meet"><path fill="#FF473E" d="M256 35.3c-121.8 0-220.6 98.8-220.6 220.6c0 2.8.1 5.6.2 8.4c-12.9 1.7-22.9 12.5-22.9 25.9c0 14.5 11.7 26.2 26.2 26.2h4.9C70.1 408.9 155.1 476.6 256 476.6c121.9 0 220.6-98.8 220.6-220.6c0-121.9-98.8-220.7-220.6-220.7z"></path><path fill="#2B3B47" d="M170.5 200.1v41.3c0 13.4-10.8 24.2-24.2 24.2c-13.4 0-24.2-10.8-24.2-24.2v-54.3l48.4 13z"></path><path fill="#2B3B47" d="M390.7 187.1v54.3c0 13.4-10.8 24.2-24.2 24.2c-13.4 0-24.2-10.8-24.2-24.2v-41.3l48.4-13z"></path><path fill="#D32A2A" d="M169.3 208.3c-.8 0-1.5-.1-2.3-.3l-51.5-15.1c-4.3-1.3-6.8-5.8-5.5-10.1c1.3-4.3 5.8-6.8 10.1-5.5l51.5 15.1c4.3 1.3 6.8 5.8 5.5 10.1c-1 3.5-4.3 5.8-7.8 5.8z"></path><path fill="#D32A2A" d="M342.2 208.3c-3.4 0-6.6-2.2-7.8-5.6c-1.4-4.3.9-8.9 5.2-10.3l54.2-17.7c4.3-1.4 8.9.9 10.3 5.2c1.4 4.3-.9 8.9-5.2 10.3l-54.2 17.7c-.8.3-1.6.4-2.5.4z"></path><path fill="#2B3B47" d="M315.7 352.9c-2.4-20.9-18.9-49.6-57.1-49.6c-39.7 0-56 31.2-57.3 52.2c-.3 5.6 2.3 10.9 6.8 14.2c4.5 3.3 10.5 4.1 15.7 2.1c.1 0 11.4-4.2 32.7-4.2c22.2 0 37.9 4.6 38.1 4.7c1.6.5 3.2.7 4.8.7h.1c9.1 0 16.5-7.4 16.5-16.5c.1-1.3-.1-2.5-.3-3.6z"></path><path fill="#D32A2A" d="M476.4 265.8c.1-3.3.3-6.6.3-9.9c0-58.9-23.1-112.4-60.8-152c21.3 34.5 33.6 75.2 33.6 118.8c0 125.2-101.5 226.6-226.7 226.6c-43.6 0-84.2-12.3-118.8-33.6c39.6 37.7 93.1 60.9 152 60.9c100.3 0 185-67 211.7-158.7h5.1c14.5 0 26.2-11.7 26.2-26.2c0-13.3-9.9-24.1-22.6-25.9z"></path></svg> <span>' . $formatted_counts['angry'] . '</span></li>';
        }

        $count_html .= $this->get_customReacts($post_id, $formatted_counts);

        $count_html .= '</ul>';

        $style = '<style>.post-reactions-list{font-size:'.$this->settings['iconSize'].'; gap: calc('.$this->settings['iconSize'].' / 2)} .post-reactions-list li svg {width: '.$this->settings['iconSize'].'}.post-reactions-list.design-1 .reacted_to{background: '.$this->settings['activeBackground'].'}</style>';
        return $count_html.$style;
    }

    // Get reactions count for a post
    private function get_reactions_count($post_id) {
        // Query the custom table and get the count for each reaction type
        // Return a formatted count

        $formatted_counts = array();

        foreach($this->allowed_reactions as $reaction){
            $formatted_counts[$reaction] = 0;
        }

        // Example:
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;
        $user_id = get_current_user_id();
        $counts = $wpdb->get_results("SELECT reaction_type, user_id, COUNT(*) as count,  MAX(CASE WHEN user_id = $user_id THEN $user_id ELSE 0 END) AS reacted FROM $table_name WHERE post_id = $post_id GROUP BY reaction_type", ARRAY_A);

        $reacted = false;
        foreach ($counts as $count) {
            $formatted_counts[$count['reaction_type']] = $count['count'];
            if($count['reacted'] == $user_id && $user_id != 0){
                $reacted = $count['reaction_type'];
            }
        }

        if(isset($_COOKIE["postReaction_$post_id"]) && $user_id == 0){
            $reacted = $_COOKIE["postReaction_$post_id"];
        }

        $formatted_counts['reacted'] = $reacted;

        return $formatted_counts;
    }

    // Check if the reaction already exists for the post
    private function reaction_exists($post_id, $reaction_type) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'post_reactions';
        $user_id = get_current_user_id();

        if($user_id === 0){
            if(isset($_COOKIE["postReaction_$post_id"]) && $_COOKIE["postReaction_$post_id"] == $reaction_type){
                return true;
            }else {
                return false;
            }
        }

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE post_id = %d AND reaction_type = %s AND user_id = %d",
                $post_id,
                $reaction_type,
                $user_id
            )
        );

        return $count > 0;
    }

    // Save the reaction in the custom table
    private function save_reaction($post_id, $reaction_type) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'post_reactions';

        $currentReact = isset($_COOKIE["postReaction_$post_id"]) ? $_COOKIE["postReaction_$post_id"] : null;

        

        if($this->settings['onlyUserCanReact'] || get_current_user_id() != 0) {
            $count = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_name WHERE post_id = %d AND user_id = %d",
                    $post_id,
                    get_current_user_id()
                )
            );
    
            if($count > 0) {
                return $this->update_reaction($post_id, $reaction_type);
            }
        }

        if($currentReact){
            return $this->update_reaction($post_id, $reaction_type);
        }

        $result = $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'reaction_type' => $reaction_type,
                'user_id' => get_current_user_id()
            ),
            array('%d', '%s')
        );

        setcookie("postReaction_$post_id", $reaction_type, time() + (86400 * 30 * 365), "/");

        return $result !== false;
    }

    // Update the reaction in the custom table
    private function update_reaction($post_id, $reaction_type) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'post_reactions';
        $currentReact = isset($_COOKIE["postReaction_$post_id"]) ? $_COOKIE["postReaction_$post_id"] : null;
        $user_id = get_current_user_id();

        // $wpdb->query($wpdb->prepare())
        
        $query = $wpdb->prepare(
            "UPDATE $table_name
            SET post_id = %d, reaction_type = %s, user_id = %d
            WHERE post_id = %d AND user_id = %d AND reaction_type = %s
            LIMIT 1",
            $post_id,
            $reaction_type,
            $user_id,
            $post_id,
            $user_id,
            $currentReact
        );

        $result = $wpdb->query($query);

        setcookie("postReaction_$post_id", $reaction_type, time() + (86400 * 30 * 365), "/");

        return $result !== false;
    }

    // Remove the reaction from the custom table
    private function remove_reaction($post_id, $reaction_type) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'post_reactions';

        $result = $wpdb->delete(
            $table_name,
            array(
                'post_id' => $post_id,
                'user_id' => get_current_user_id(),
                'reaction_type' => $reaction_type,
            ),
            array('%d', '%d', '%s')
        );

        setcookie("postReaction_$post_id", 'content', 1, "/");

        return $result !== false;
    }

    private function get_customReacts($post_id, $formatted_counts) {
        $customReacts = '';

        
        if(is_array($this->settings['customReacts']) ?? []){
            foreach($this->settings['customReacts'] as $react){
                if(isset($react['id']) && $this->isset($react, 'enabled') === true){
                    $this->allowed_reactions = array_merge($this->allowed_reactions, [$react['id']]);
                    $icon = $react['svg'] ? base64_decode($react['svg']) : '<span class="prc_react_icon">'.$react['id'].'</span>';
                    $count = $formatted_counts[$react['id']] ?? 0;
                    $class = $formatted_counts['reacted'] === $react['id'] ? 'reacted_to' : '';
                    $customReacts .= '<li title="'.$react['name'].'" class="'.$class.'" data-post-id="'.$post_id.'" data-reaction-type="'.$react['id'].'">'.$icon.'<span>' . $count . '</span></li>';
                }
            }
        }

        return $customReacts;
    }

    private function isset($array, $key){
        return $array[$key] ?? false;
    }


    private function getSettings(){
        return wp_parse_args( json_decode(get_option('cprSettings'), true), [
            'enabled' => true,
            'customReacts' => [],
            'enabledReacts' => [],
            'afterContent' => '',
            'beforeContent' => '',
            'postTypes' => ['post'],
            'contentPosition' => 'after_content',
            'onlyUserCanReact' => true,
            "iconSize" => "20px",
            "activeBackground" =>"#12ff0045", 
            "design" => "design-1"
        ] );
    }

    public function footerAlert(){
        echo "<img src='".PRC_PLUGIN_DIR ."assets/svg/sprite.svg' alt='' />";
        echo '<svg class="icon"> <use xlink:href="#angry"></use> </svg><svg class="icon"> <use xlink:href="#heart"></use> </svg><svg class="icon"> <use xlink:href="#thumbs-up"></use> </svg><svg class="icon"> <use xlink:href="#wow"></use> </svg>';
        echo "<div class='cprAlert'><svg width='40px' height='40px' viewBox='0 0 76 76'> <path fill='#ffffff' fill-opacity='1' stroke-width='0.2' stroke-linejoin='round' d='M 38,22.1667C 41.1666,22.1667 57,52.25 55.4166,53.8333C 53.8333,55.4167 22.1667,55.4167 20.5833,53.8333C 19,52.25 34.8333,22.1667 38,22.1667 Z M 38,45.9167C 36.6883,45.9167 35.625,46.98 35.625,48.2917C 35.625,49.6034 36.6883,50.6667 38,50.6667C 39.3116,50.6667 40.375,49.6034 40.375,48.2917C 40.375,46.98 39.3116,45.9167 38,45.9167 Z M 35.625,31.6667L 36.4166,44.3333L 39.5833,44.3333L 40.375,31.6667L 35.625,31.6667 Z '/> </svg><span>Please login to react!</span></div>";
    }

}