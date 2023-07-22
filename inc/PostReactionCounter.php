<?php
namespace PostReaction;

class PostReactionCounter{

    private $table_name = 'post_reactions';
    private $allowed_reactions = array('like', 'love', 'wow', 'angry');
    public function register() {
        // Register hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_update_post_reaction', array($this, 'save_reaction_callback'));
        add_action('wp_ajax_nopriv_update_post_reaction', array($this, 'save_reaction_callback'));
        add_action('the_content', array($this, 'display_reactions_count'));
    }

    // Enqueue frontend scripts and styles
    public function enqueue_scripts() {
        wp_enqueue_style('post-reactions', PRC_PLUGIN_DIR . 'dist/style.css', array(), PRC_VER, 'all');

        wp_enqueue_script('post-reactions-script', PRC_PLUGIN_DIR . 'dist/public.js', array('jquery'), PRC_VER, true);
        wp_localize_script( 'post-reactions-script', 'postReactScript', [
            'ajaxURL' => admin_url('admin-ajax.php'),
        ] );
    }

    // AJAX callback to save reaction
    public function save_reaction_callback() {
        // Check the AJAX nonce for security
        // check_ajax_referer('post_reactions_counter_nonce', 'security');

        // Handle the AJAX request here
        // Retrieve and sanitize the data
        // Save the reaction in the custom table

        // Example: 
        $post_id = absint($_POST['post_id']);
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
        $reactions_count = $this->get_reactions_contents($post->ID);

        // Format and add the count to the content
        $count_html = sprintf('<div class="post-reactions-count">%s</div>', $reactions_count);
        return $content . $count_html;
    }

    private function get_reactions_contents($post_id){

        $formatted_counts = $this->get_reactions_count($post_id);
        $reacted = $formatted_counts['reacted'];

        $count_html = '<ul class="post-reactions-list">';

        $count_html .= '<li class="" data-post-id="'.$post_id.'" data-reaction-type="like"><svg fill="#000000" xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" viewBox="0 0 52 52" enable-background="new 0 0 52 52" xml:space="preserve"> <g> <path d="M10.5,21h-5C4.7,21,4,21.7,4,22.5v23C4,46.3,4.7,47,5.5,47H8c2.2,0,4-1.8,4-4V22.5C12,21.7,11.3,21,10.5,21 z"/> <path d="M44,22h-6c-2.2,0-4-1.8-4-4V8c0-2.2-1.8-4-4-4h-2.5C26.7,4,26,4.7,26,5.5v6c0,5.3-3.7,10.5-8.5,10.5 c-0.8,0-1.5,0.7-1.5,1.5v20c0,0.8,0.6,1.5,1.4,1.5c6.8,0.3,9.1,3,16.2,3c7.5,0,14.4-0.8,14.4-9.5v-5V26C48,23.8,46.2,22,44,22z"/> </g> </svg> <span>' . $formatted_counts['like'] . '</span></li>';

        $count_html .= '<li data-post-id="'.$post_id.'" data-reaction-type="love"><svg width="20px" height="20px" viewBox="0 0 24 24" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/">
        <g transform="translate(0 -1028.4)">
         <path d="m7 1031.4c-1.5355 0-3.0784 0.5-4.25 1.7-2.3431 2.4-2.2788 6.1 0 8.5l9.25 9.8 9.25-9.8c2.279-2.4 2.343-6.1 0-8.5-2.343-2.3-6.157-2.3-8.5 0l-0.75 0.8-0.75-0.8c-1.172-1.2-2.7145-1.7-4.25-1.7z" fill="#e74c3c"/>
        </g>
       </svg> <span>' . $formatted_counts['love'] . '</span></li>';

        $count_html .= '<li  data-post-id="'.$post_id.'" data-reaction-type="wow"><svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="12" r="9.5" stroke="#222222" stroke-linecap="round"/>
        <circle cx="9" cy="9" r="1" fill="#222222" stroke="#222222" stroke-linecap="round"/>
        <circle cx="15" cy="9" r="1" fill="#222222" stroke="#222222" stroke-linecap="round"/>
        <path d="M15 15.5C15 16.8807 13.6569 18 12 18C10.3431 18 9 16.8807 9 15.5C9 14.1193 10.3431 13 12 13C13.6569 13 15 14.1193 15 15.5Z" fill="#222222"/>
        </svg> <span>' . $formatted_counts['wow'] . '</span></li>';

        $count_html .= '<li  data-post-id="'.$post_id.'" data-reaction-type="angry"><svg width="20px" height="20px" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--fxemoji" preserveAspectRatio="xMidYMid meet"><path fill="#FF473E" d="M256 35.3c-121.8 0-220.6 98.8-220.6 220.6c0 2.8.1 5.6.2 8.4c-12.9 1.7-22.9 12.5-22.9 25.9c0 14.5 11.7 26.2 26.2 26.2h4.9C70.1 408.9 155.1 476.6 256 476.6c121.9 0 220.6-98.8 220.6-220.6c0-121.9-98.8-220.7-220.6-220.7z"></path><path fill="#2B3B47" d="M170.5 200.1v41.3c0 13.4-10.8 24.2-24.2 24.2c-13.4 0-24.2-10.8-24.2-24.2v-54.3l48.4 13z"></path><path fill="#2B3B47" d="M390.7 187.1v54.3c0 13.4-10.8 24.2-24.2 24.2c-13.4 0-24.2-10.8-24.2-24.2v-41.3l48.4-13z"></path><path fill="#D32A2A" d="M169.3 208.3c-.8 0-1.5-.1-2.3-.3l-51.5-15.1c-4.3-1.3-6.8-5.8-5.5-10.1c1.3-4.3 5.8-6.8 10.1-5.5l51.5 15.1c4.3 1.3 6.8 5.8 5.5 10.1c-1 3.5-4.3 5.8-7.8 5.8z"></path><path fill="#D32A2A" d="M342.2 208.3c-3.4 0-6.6-2.2-7.8-5.6c-1.4-4.3.9-8.9 5.2-10.3l54.2-17.7c4.3-1.4 8.9.9 10.3 5.2c1.4 4.3-.9 8.9-5.2 10.3l-54.2 17.7c-.8.3-1.6.4-2.5.4z"></path><path fill="#2B3B47" d="M315.7 352.9c-2.4-20.9-18.9-49.6-57.1-49.6c-39.7 0-56 31.2-57.3 52.2c-.3 5.6 2.3 10.9 6.8 14.2c4.5 3.3 10.5 4.1 15.7 2.1c.1 0 11.4-4.2 32.7-4.2c22.2 0 37.9 4.6 38.1 4.7c1.6.5 3.2.7 4.8.7h.1c9.1 0 16.5-7.4 16.5-16.5c.1-1.3-.1-2.5-.3-3.6z"></path><path fill="#D32A2A" d="M476.4 265.8c.1-3.3.3-6.6.3-9.9c0-58.9-23.1-112.4-60.8-152c21.3 34.5 33.6 75.2 33.6 118.8c0 125.2-101.5 226.6-226.7 226.6c-43.6 0-84.2-12.3-118.8-33.6c39.6 37.7 93.1 60.9 152 60.9c100.3 0 185-67 211.7-158.7h5.1c14.5 0 26.2-11.7 26.2-26.2c0-13.3-9.9-24.1-22.6-25.9z"></path></svg> <span>' . $formatted_counts['angry'] . '</span></li>';

        $count_html .= '</ul>';

        return $count_html;
    }

    // Get reactions count for a post
    private function get_reactions_count($post_id) {
        // Query the custom table and get the count for each reaction type
        // Return a formatted count

        // Example:
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;
        $counts = $wpdb->get_results("SELECT reaction_type, user_id, COUNT(*) as count FROM $table_name WHERE post_id = $post_id GROUP BY reaction_type", ARRAY_A);
        $formatted_counts = array(
            'love' => 0,
            'angry' => 0,
            'like' => 0,
            'wow' => 0
        );
        $reacted = false;
        foreach ($counts as $count) {
            $formatted_counts[$count['reaction_type']] = $count['count'];
            if($count['user_id'] == get_current_user_id()){
                $reacted = $count['reaction_type'];
            }
        }

        $formatted_counts['reacted'] = $reacted;

        return $formatted_counts;
    }

    // Check if the reaction already exists for the post
    private function reaction_exists($post_id, $reaction_type) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'post_reactions';

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE post_id = %d AND reaction_type = %s AND user_id = %d",
                $post_id,
                $reaction_type,
                get_current_user_id()
            )
        );

        return $count > 0;
    }

    // Save the reaction in the custom table
    private function save_reaction($post_id, $reaction_type) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'post_reactions';

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

        $result = $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'reaction_type' => $reaction_type,
                'user_id' => get_current_user_id()
            ),
            array('%d', '%s')
        );

        return $result !== false;
    }

    // Update the reaction in the custom table
    private function update_reaction($post_id, $reaction_type) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'post_reactions';

        $result = $wpdb->update(
            $table_name,
            array(
                'post_id' => $post_id,
                'reaction_type' => $reaction_type,
                'user_id' => get_current_user_id()
            ),
            array(
                'post_id' => $post_id,
                'user_id' => get_current_user_id( )
            ),
            array('%d', '%s')
        );

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

        return $result !== false;
    }

}