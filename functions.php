<?php
//检测主题更新
require_once get_template_directory() . '/include/theme-update-checker.php';
$origami_update_checker = new ThemeUpdateChecker(
  'Origami',
  'https://lab.ixk.me/wordpress/Origami-theme-info.json'
);

/**
 * Update设置
 */
$origami_version = wp_get_theme()->get('Version');
if ($origami_version <= 1.0 && !get_option('origami_first_install')) {
  update_option('origami_first_install', true);
}

// 用来发送安装信息，只会在安装后调用一次
if (get_option('origami_first_install') != "ok") {
  $header = array(
    'http' => array('method' => "GET")
  );
  $header = stream_context_create($header);
  $key = file_get_contents(
    'http://lab.ixk.me/wordpress/Origami-install-count.php?type=get-key&site-url=' .
      $_SERVER['HTTP_HOST'],
    false,
    $header
  );
  update_option('origami_theme_key', $key);
  update_option('origami_first_install', 'ok');
}

/**
 * 加载功能
 */
add_theme_support('title-tag');
add_theme_support('post-thumbnails');
add_filter('pre_option_link_manager_enabled', '__return_true');
register_nav_menus(['main-menu' => esc_html__('主菜单')]);
// 加载主题设置
require get_template_directory() . '/include/customizer.php';
// Ajax提交评论注入
require 'ajax-comment/main.php';
/**
 * 加载主要资源
 */
if (!is_admin()) {
  // 加载主要css/js文件
  // wp_enqueue_style(
  //   'origami-style',
  //   get_stylesheet_uri(),
  //   array(),
  //   wp_get_theme()->get('Version')
  // );
  wp_enqueue_script('comment-reply');
  wp_enqueue_script(
    'jquery-script',
    get_template_directory_uri() . '/js/jquery-3.3.1.min.js',
    array(),
    wp_get_theme()->get('Version')
  );
  wp_enqueue_script(
    'origami-script',
    get_template_directory_uri() . '/js/main.js',
    array(),
    wp_get_theme()->get('Version')
  );
  wp_enqueue_script(
    'qrcode-script',
    get_template_directory_uri() . '/js/qrcode.min.js',
    array(),
    wp_get_theme()->get('Version')
  );
  function origami_load_font_awesome()
  {
    wp_enqueue_style(
      'font-awesome',
      get_template_directory_uri() . '/css/font-awesome.min.css'
    );
  }
  add_action('wp_footer', 'origami_load_font_awesome');
  // canvas-nest加载
  if (get_option('origami_canvas_nest') == true) {
    function origami_setting_canvas_nest()
    {
      echo '<script type="text/javascript" color="0,0,0" zindex="-1" opacity="0.5" count="99" src="' .
        get_template_directory_uri() .
        '/js/canvas-nest.js"></script>';
    }
    add_action('wp_footer', 'origami_setting_canvas_nest', '99');
  }
  // owo 表情加载
  function origami_load_owo()
  {
    if (get_option('origami_comment_owo') == true) {
      wp_enqueue_style(
        'owo-style',
        get_template_directory_uri() . '/css/OwO.min.css'
      );
      wp_enqueue_script(
        'owo-script',
        get_template_directory_uri() . '/js/OwO.min.js'
      );
    }
  }
  // 文章目录加载
  function origami_load_tocbot()
  {
    wp_enqueue_style(
      'tocbot-style',
      get_template_directory_uri() . '/css/tocbot.css'
    );
    wp_enqueue_script(
      'tocbot-script',
      get_template_directory_uri() . '/js/tocbot.min.js'
    );
  }
  // 加载代码高亮
  function origami_load_prism()
  {
    wp_enqueue_style(
      'prism-style',
      get_template_directory_uri() . '/css/prism.css'
    );
    wp_enqueue_script(
      'prism-script',
      get_template_directory_uri() . '/js/prism.js'
    );
  }
  // 加载Lazyload
  function origami_load_lazyload()
  {
    $config = get_option('origami_lazyload');
    if (stripos($config, ',') == true) {
      $config = explode(',', $config);
    } else {
      $config = array('false');
    }
    if (strcmp($config[0], 'true') == 0) {
      wp_enqueue_script(
        'lazyload-script',
        get_template_directory_uri() . '/js/lazyload.min.js'
      );
    }
  }
  add_action('wp_footer', 'origami_load_lazyload');
  // 加载WorkBox
  if (get_option('origami_workbox') == true) {
    function origami_setting_workbox()
    {
      echo "<script>if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                  navigator.serviceWorker.register('/sw.js');
                });
            }</script>";
    }
    add_action('wp_footer', 'origami_setting_workbox', '101');
  } else {
    function origami_remove_workbox()
    {
      echo "<script>window.addEventListener('load', () => {
                navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) {
                    registration.unregister()
                } });})</script>";
    }
    add_action('wp_footer', 'origami_remove_workbox', '101');
  }
} else {
  // 加载后台编辑器样式
  function origami_mce_css($mce_css)
  {
    if (!empty($mce_css)) {
      $mce_css .= ',';
    }
    $mce_css .= get_template_directory_uri() . '/css/admin-css.css';
    return $mce_css;
  }
  add_filter('mce_css', 'origami_mce_css');
  function origami_copyright_warn()
  {
    $origami_footer_content = file_get_contents(
      get_theme_file_path() . "/footer.php"
    );
    if (
      stripos($origami_footer_content, "www.ixk.me") == false ||
      stripos($origami_footer_content, "origami-theme-info") == false
    ) {
      function origami_add_warn()
      {
        echo '<div class="notice notice-warning is-dismissible">
                    <p>Warning：你可能修改了页脚的版权信息，请将其修正。Origami主题要求你保留页脚主题信息。</p>
                </div>';
      }
      add_action('admin_notices', 'origami_add_warn');
    }
  }
  add_action('admin_menu', 'origami_copyright_warn');
}

/**
 * 添加古腾堡资源
 */
function origami_load_blocks()
{
  wp_enqueue_script(
    'origami_block_js',
    get_template_directory_uri() . '/js/blocks.build.js',
    array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
    true
  );
}
add_action('enqueue_block_editor_assets', 'origami_load_blocks');

/**
 * 异步加载JS
 */
function origami_async_script($tag, $handle, $src)
{
  $async_method = 'async'; // 可将“async”改为“defer”
  $async_exclusions = 'jquery-3.3.1.min.js'; // 排除的JS
  $array_exclusions = !empty($async_exclusions)
    ? explode(',', $async_exclusions)
    : array();
  if (false === is_admin()) {
    if (!empty($array_exclusions)) {
      foreach ($array_exclusions as $exclusion) {
        $exclusion = trim($exclusion);
        if ($exclusion != '') {
          if (false !== strpos(strtolower($src), strtolower($exclusion))) {
            return $tag;
          }
        }
      }
    }
    $tag = str_replace(
      'src=',
      $async_method . "='" . $async_method . "' src=",
      $tag
    );
    return $tag;
  }
  return $tag;
}
add_filter('script_loader_tag', 'origami_async_script', 10, 3);

/**
 * 块编辑器
 */
// // 去除 Wordpress5.0 块编辑器样式
// function fanly_remove_block_library_css()
// {
//     wp_dequeue_style('wp-block-library');
// }
// add_action('wp_enqueue_scripts', 'fanly_remove_block_library_css', 100);
// // 禁用块编辑器
// add_filter('use_block_editor_for_post', '__return_false');
// remove_action('wp_enqueue_scripts', 'wp_common_block_scripts_and_styles');

/**
 * 使评论支持图片
 */
function origami_auto_comment_image($comment)
{
  global $allowedtags;
  $allowedtags['img'] = array('src' => array(), 'alt' => array());
  return $comment;
}
add_filter('preprocess_comment', 'origami_auto_comment_image');

/*
 * 移除多余代码
 */
// 去除默认jquery
function remove_jquery()
{
  wp_deregister_script('jquery');
}
add_action('wp_footer', 'remove_jquery');
// 去除表情
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
//去除头部无用代码
add_filter('show_admin_bar', '__return_false');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
remove_action('wp_head', 'locale_stylesheet');
remove_action('wp_head', 'noindex', 1);
remove_action('wp_head', 'wp_print_head_scripts', 9);
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'rel_canonical');
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
remove_action('wp_head', 'wp_oembed_add_host_js');
remove_action('wp_head', 'wp_resource_hints', 2);
remove_action('wp_head', 'rest_output_link_wp_head', 10);
remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
remove_action('wp_footer', 'wp_print_footer_scripts');
remove_action('publish_future_post', 'check_and_publish_future_post', 10, 1);
remove_action('template_redirect', 'wp_shortlink_header', 11, 0);
remove_action('template_redirect', 'rest_output_link_header', 11, 0);
remove_action('rest_api_init', 'wp_oembed_register_route');
remove_filter(
  'rest_pre_serve_request',
  '_oembed_rest_pre_serve_request',
  10,
  4
);
remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
remove_filter('oembed_response_data', 'get_oembed_response_data_rich', 10, 4);
add_action('comment_unapproved_to_approved', 'sirius_comment_approved');

/**
 * 添加面包屑导航
 */
function origami_breadcrumbs($echo = true)
{
  $breadcrumbs = [];
  if ((!is_home() && !is_front_page()) || is_paged()) {
    global $post;
    $homeLink = home_url();
    $breadcrumbs[] = [
      "name" => __("Home"),
      "link" => $homeLink
    ];
    if (is_category()) {
      $arr = [];
      global $wp_query;
      $cat_obj = $wp_query->get_queried_object();
      $thisCat = $cat_obj->cat_ID;
      $thisCat = get_the_category($thisCat)[0];
      $arr[] = [
        "name" => $thisCat->name,
        "link" => get_category_link($thisCat->cat_ID)
      ];
      $parentCat = get_the_category($thisCat->parent)[0];
      while (
        $parentCat->cat_ID != $thisCat->cat_ID &&
        $parentCat->cat_ID != 0
      ) {
        $arr[] = [
          "name" => $parentCat->name,
          "link" => get_category_link($parentCat->car_ID)
        ];
        $parentCat = get_the_category($parentCat->parent)[0];
      }
      for ($i = count($arr) - 1; $i >= 0; $i--) {
        $breadcrumbs[] = $arr[$i];
      }
    } elseif (is_day()) {
      $breadcrumbs[] = [
        "link" => get_year_link(get_the_time('Y')),
        "name" => get_the_time('Y')
      ];
      $breadcrumbs[] = [
        "link" => get_month_link(get_the_time('Y'), get_the_time('m')),
        "name" => get_the_time('F')
      ];
      $breadcrumbs[] = [
        "name" => get_the_time('d'),
        "link" => false
      ];
    } elseif (is_month()) {
      $breadcrumbs[] = [
        "link" => get_year_link(get_the_time('Y')),
        "name" => get_the_time('Y')
      ];
      $breadcrumbs[] = [
        "link" => false,
        "name" => get_the_time('F')
      ];
    } elseif (is_year()) {
      $breadcrumbs[] = [
        "link" => false,
        "name" => get_the_time('Y')
      ];
    } elseif (is_single() && !is_attachment()) {
      // 文章
      if (get_post_type() != 'post') {
        // 自定义文章类型
        $post_type = get_post_type_object(get_post_type());
        $slug = $post_type->rewrite;
        $breadcrumbs[] = [
          "link" => $homeLink . '/' . $slug['slug'] . '/',
          "name" => $post_type->labels->singular_name
        ];
        $breadcrumbs[] = [
          "link" => false,
          "name" => get_the_title()
        ];
      } else {
        $arr = [];
        $thisCat = get_the_category()[0];
        $arr[] = [
          "name" => $thisCat->name,
          "link" => get_category_link($thisCat->cat_ID)
        ];
        $parentCat = get_the_category($thisCat->parent)[0];
        while (
          $parentCat->cat_ID != $thisCat->cat_ID &&
          $parentCat->cat_ID != 0
        ) {
          $arr[] = [
            "name" => $parentCat->name,
            "link" => get_category_link($parentCat->cat_ID)
          ];
          if ($parentCat->parent == 0) {
            break;
          }
          $parentCat = get_the_category($parentCat->parent)[0];
        }
        for ($i = count($arr) - 1; $i >= 0; $i--) {
          $breadcrumbs[] = $arr[$i];
        }
        $breadcrumbs[] = [
          "link" => false,
          "name" => get_the_title()
        ];
      }
    } elseif (!is_single() && !is_page() && get_post_type() != 'post') {
      $post_type = get_post_type_object(get_post_type());
      $breadcrumbs[] = [
        "link" => false,
        "name" => $post_type->labels->singular_name
      ];
    } elseif (is_attachment()) {
      $parent = get_post($post->post_parent);
      $breadcrumbs[] = [
        "link" => get_permalink($parent),
        "name" => $parent->post_title
      ];
      $breadcrumbs[] = [
        "link" => false,
        "name" => get_the_title()
      ];
    } elseif (is_page() && !$post->post_parent) {
      $breadcrumbs[] = [
        "link" => false,
        "name" => get_the_title()
      ];
    } elseif (is_page() && $post->post_parent) {
      $parent_id = $post->post_parent;
      $bread = [];
      while ($parent_id) {
        $page = get_page($parent_id);
        $bread[] = [
          "link" => get_permalink($page->ID),
          "name" => get_the_title($page->ID)
        ];
        $parent_id = $page->post_parent;
      }
      for ($i = count($bread) - 1; $i >= 0; $i--) {
        $breadcrumbs[] = $bread[i];
      }
      $breadcrumbs[] = [
        "link" => false,
        "name" => get_the_title()
      ];
    } elseif (is_search()) {
      $breadcrumbs[] = [
        "link" => false,
        "name" => sprintf(__('Search Results for: %s'), get_search_query())
      ];
    } elseif (is_tag()) {
      $breadcrumbs[] = [
        "link" => false,
        "name" => sprintf(__('Tag Archives: %s'), single_tag_title('', false))
      ];
    } elseif (is_author()) {
      // 作者存档
      global $author;
      $userdata = get_userdata($author);
      $breadcrumbs[] = [
        "link" => false,
        "name" => sprintf(__('Author Archives: %s'), $userdata->display_name)
      ];
    } elseif (is_404()) {
      $breadcrumbs[] = [
        "link" => false,
        "name" => _e('Not Found')
      ];
    }
    if (get_query_var('paged')) {
      if (
        is_category() ||
        is_day() ||
        is_month() ||
        is_year() ||
        is_search() ||
        is_tag() ||
        is_author()
      ) {
        $breadcrumbs[] = [
          "link" => false,
          "name" => sprintf(__('( Page %s )'), get_query_var('paged'))
        ];
      }
    }
  }
  $str = "";
  if ($echo) {
    foreach ($breadcrumbs as $item) {
      $str .=
        '<li class="breadcrumb-item"><a href="' .
        $item['link'] .
        '">' .
        $item['name'] .
        '</a></li>';
    }
    echo '<ul class="breadcrumb">' . $str . '</ul>';
  }
}

/**
 * 页脚时间
 */
function origami_footer_time_fun()
{
  $origami_footer_time = get_option('origami_footer_time');
  if ($origami_footer_time != '') {
    echo '
            <script>// <![CDATA[
            var now = new Date();
            function createtime(){ var grt= new Date("' .
      $origami_footer_time .
      '");
            now.setTime(now.getTime()+250);
            days = (now - grt ) / 1000 / 60 / 60 / 24; dnum = Math.floor(days);
            hours = (now - grt ) / 1000 / 60 / 60 - (24 * dnum); hnum = Math.floor(hours);
            if(String(hnum).length ==1 ){hnum = "0" + hnum;} minutes = (now - grt ) / 1000 /60 - (24 * 60 * dnum) - (60 * hnum);
            mnum = Math.floor(minutes); if(String(mnum).length ==1 ){mnum = "0" + mnum;}
            seconds = (now - grt ) / 1000 - (24 * 60 * 60 * dnum) - (60 * 60 * hnum) - (60 * mnum);
            snum = Math.round(seconds); if(String(snum).length ==1 ){snum = "0" + snum;}
            document.getElementById("timeDate").innerHTML = ""+dnum+"天";
            document.getElementById("times").innerHTML = hnum + "小时" + mnum + "分" + snum + "秒"; }
            setInterval("createtime()",250);
            // ]]></script>
        ';
  }
}
add_action('wp_footer', 'origami_footer_time_fun');

/**
 * 评论回调
 */
function origami_comment($comment, $args, $depth)
{
  /* 评论者标签 - start */
  global $wpdb;
  $comment_mark = "";
  $comment_mark_color = "#CBBBBA";
  //站长邮箱
  $adminEmail = get_option('admin_email');
  //从数据库读取有人链接
  $linkurls = $wpdb->get_results("SELECT link_url FROM wp_links", "ARRAY_N");
  $other_friend_links = explode(',', get_option('origami_other_friends'));
  foreach ($other_friend_links as $other_friend_link) {
    $other_friend_links_arr[][0] = $other_friend_link;
  }
  $linkurls = array_merge($linkurls, $other_friend_links_arr);
  //默认不是朋友，将标记为访客
  $is_friend = false;
  //判断是不是站长我
  if ($comment->comment_author_email == $adminEmail) {
    $comment_mark =
      '<a target="_blank" href="/关于我" title="经鉴定，这货是站长">站长</a>';
    $comment_mark_color = "#0bf";
    $is_friend = true;
  }
  if (!$is_friend && $comment->comment_author_url != '') {
    $rex = '/(https:\/\/|http:\/\/)[a-z0-9-]*\.([a-z0-9-]+\.[a-z]+).*/i';
    $rex2 = '/(https:\/\/|http:\/\/)([a-z0-9-]+\.[a-z]+).*/i';
    if (substr_count($comment->comment_author_url, '.') == 2) {
      preg_match($rex, $comment->comment_author_url, $author_url_re);
    } else {
      preg_match($rex2, $comment->comment_author_url, $author_url_re);
    }
    $comment_author_url_reg = $author_url_re[2];
    foreach ($linkurls as $linkurl) {
      if (substr_count($linkurl[0], '.') == 2) {
        preg_match($rex, $linkurl[0], $url_re);
      } else {
        preg_match($rex2, $linkurl[0], $url_re);
      }
      if ($comment_author_url_reg == $url_re[2]) {
        $comment_mark =
          '<a target="_blank" href="/links" title="友情链接认证">友人</a>';
        $comment_mark_color = "#5EBED2";
        $is_friend = true;
      }
    }
  }
  //若不在列表中就标记为访客
  if ($is_friend == false) {
    $comment_mark = "访客";
  }
  $comment_mark =
    '<div class="comment-mark" style="background:' .
    $comment_mark_color .
    '">' .
    $comment_mark .
    '</div>';

  /* 评论者标签 - end */
  ?>

    <div id="comment-<?php echo $comment->comment_ID; ?>" class="comment-level-<?php echo $depth; ?> comment-list">
        <div class="comment-left">
            <?php echo get_avatar($comment, 64); ?>
        </div>
        <div class="comment-right">
            <div class="comment-header">
                <div class="comment-author">
                    <?php if ($comment->comment_author_url != "") {
                      echo '<a href="' .
                        $comment->comment_author_url .
                        '">' .
                        $comment->comment_author .
                        '</a>';
                    } else {
                      echo $comment->comment_author;
                    } ?>
                </div>
                <?php echo $comment_mark; ?>
            </div>
            <div class="comment-text">
                <?php comment_text(); ?>
            </div>
            <div class="comment-footer">
                <div class="comment-footer-left">
                <div class="comment-date"><i class="fa fa-clock-o" aria-hidden="true"></i>发表于: <?php echo get_comment_time(
                  'Y-m-d H:i'
                ); ?></div>
                </div>
                <div class="comment-footer-right">
                    <span><?php edit_comment_link('修改'); ?></span>
                    <span title="回复">
                        <?php if ($depth < $args['max_depth']) {
                          echo '<i class="fa fa-reply" aria-hidden="true"></i>';
                        } ?>
                        <?php comment_reply_link(
                          array_merge($args, array(
                            'reply_text' => '回复',
                            'depth' => $depth,
                            'max_depth' => $args['max_depth']
                          ))
                        ); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
<?php
}

/**
 * 评论加@
 */
function origami_comment_add_at($comment_text, $comment = '')
{
  if ($comment->comment_parent > 0) {
    $comment_text =
      '<a rel="nofollow" class="comment_at" href="#comment-' .
      $comment->comment_parent .
      '">@' .
      get_comment_author($comment->comment_parent) .
      '：</a> ' .
      $comment_text;
  }
  return $comment_text;
}
add_filter('comment_text', 'origami_comment_add_at', 10, 2);

/**
 * 前端获取评论
 * ?action=comments&post_id=...page_index=...
 */
function origami_load_comments()
{
  if (isset($_GET['action'])) {
    if ($_GET['action'] == 'comments') {
      $comments = get_comments(array(
        'post_id' => $_GET['post_id'],
        'status' => 'approve'
      ));
      // 反转评论列表，使最新最新评论显示在上方
      $comments = array_reverse($comments);
      // 存入前端传来的页码
      $page_index = $_GET['page_index'];
      // 输出评论
      echo '<div id="comment-wrapper" class="comment-wrapper">';
      wp_list_comments(
        'type=comment&callback=origami_comment&reverse_top_level=true&per_page=10&page=' .
          $page_index,
        $comments
      );
      echo '</div>';
      echo '<div id="comment_page_index" style="display:none">' .
        $page_index .
        '</div>';
      die();
    }
  }
}
add_action('init', 'origami_load_comments');

/**
 * 有新评论时发送邮件通知
 */
function origami_comment_respond_email($comment_id, $comment)
{
  if ($comment->comment_approved == 1 && $comment->comment_parent > 0) {
    $comment_parent_author_email = get_comment_author_email(
      $comment->comment_parent
    );
    // 站点信息
    $blog_url = wp_specialchars_decode(home_url(), ENT_QUOTES);
    $blog_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    // 回复邮件信息
    $comment = get_comment($comment_id);
    $comment_author_name = $comment->comment_author;
    // 被回复邮件信息
    $comment_parent = get_comment($comment->comment_parent);
    $comment_post_id = $comment_parent->comment_post_ID;
    $comment_parent_author_name = $comment_parent->comment_author;

    $headers =
      "Content-Type: text/html; charset=\"" .
      get_option('blog_charset') .
      "\"\n";

    $subject = '你在 [' . get_option('blogname') . '] 上的评论有了新回复。';

    $message =
      '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <meta name="viewport" content="width=device-width" />
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <title>你在[' .
      $blog_name .
      ']上的评论有了新回复。</title>
        </head>
        <body>
            <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" style="min-width: 348px;background-color: #EEEEEE;">
            	<tbody>
            		<tr height="32px"></tr>
            		<tr align="center">
            			<td width="32px"></td>
            			<td>
            				<table border="0" cellspacing="0" cellpadding="0" style="max-width:600px">
            					<tbody>
            						<tr>
            							<td>
            								<table width="100%" border="0" cellspacing="0" cellpadding="0">
            									<tbody>
            										<tr>
            											<td align="left" style="font-size: 30px;color:#40C4FF;">
            												<span>Otstar</span>&nbsp;<span style="color:#8bb7c5">Cloud</span>
            											</td>
            											<td align="right">
            												<img width="32" height="32" style="display:block;width: 45px;height: 45px;border-radius:50%;" alt="avatar" src="https://www.ixk.me/avatar-lite.png">
            											</td>
            										</tr>
            									</tbody>
            								</table>
            							</td>
            						</tr>
            						<tr height="16"></tr>
            						<tr>
            							<td>
            								 <table bgcolor="#40C4FF" width="100%" border="0" cellspacing="0" cellpadding="0" style="min-width:332px;max-width:600px;border:1px solid #e0e0e0;border-bottom:0;border-top-left-radius:3px;border-top-right-radius:3px">
            									<tbody>
            										<tr>
            											<td height="50px" colspan="3"></td>
            										</tr>
            										<tr>
            											<td style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:30px;color:#ffffff;line-height:1.25;text-align:center">您的评论有新回复</td>
            											<td width="32px"></td>
            										</tr>
            										<tr>
            											<td height="30px" colspan="3"></td>
            										</tr>
            									</tbody>
            								</table>
            							</td>
            						</tr>
            						<tr>
            							<td>
            								<table bgcolor="#FAFAFA" width="100%" border="0" cellspacing="0" cellpadding="0" style="min-width:332px;max-width:600px;border:1px solid #f0f0f0;border-bottom:1px solid #c0c0c0;border-top:0;border-bottom-left-radius:3px;border-bottom-right-radius:3px">
            									<tbody>
            										<tr height="16px">
            											<td width="32px" rowspan="3"></td>
            											<td></td>
            											<td width="32px" rowspan="3"></td>
            										</tr>
            										<tr>
            											<td>
            												<table style="min-width:300px" border="0" cellspacing="0" cellpadding="0">
            													<tbody>
            														<tr>
            															<td style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:13px;color:#202020;line-height:1.5">尊敬的<span style="color:#40ceff;font-weight:bold">' .
      $comment_parent_author_name .
      '</span>，您好！</td>
            														</tr>
            														<tr>
            															<td style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:13px;color:#202020;line-height:1.5">您对[' .
      $blog_name .
      ']上 《<span style="white-space:nowrap;color:#40ceff" href="' .
      get_permalink($comment_post_id) .
      '">' .
      get_the_title($comment_post_id) .
      '</span>》 一文的评论有新回复，欢迎您前来继续参与讨论。<br><br>这是您发表的原始评论<ol style="background:#e0e0e0;margin:5px;padding:20px 40px 20px">' .
      $comment_parent->comment_content .
      '</ol><span style="color:#40ceff;font-weight:bold">' .
      $comment_author_name .
      '</span>给您的回复如下<ol style="background:#e0e0e0;margin:5px;padding:20px 40px 20px">' .
      $comment->comment_content .
      '</ol><br>如有需要，您可以<a style="text-decoration:none;color:#4285f4" target="_blank" href="' .
      get_comment_link($comment_parent->comment_ID) .
      '">查看有关此回复的详细信息</a>。 </td>
            														</tr>
            														<tr height="26px"></tr>
            														<tr>
            															<td style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:13px;color:#202020;line-height:1.5">此致<br>' .
      $blog_name .
      '敬上</td>
            														</tr>
            														<tr height="20px"></tr>
            														<tr>
            															<td>
            																<table style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:12px;color:#b9b9b9;line-height:1.5">
            																	<tbody>
            																		<tr>
            																			<td>此电子邮件地址可接收回复。如需更多信息，请访问<a href="https://blog.syfxlin.win/%E5%85%B3%E4%BA%8E%E6%88%91" style="text-decoration:none;color:#4285f4" target="_blank"> 关于我</a>。</td>
            																		</tr>
            																	</tbody>
            																</table>
            															</td>
            														</tr>
            													</tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr height="32px"></tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr height="16"></tr>
                                    <tr>
                                        <td style="max-width:600px;font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:10px;color:#bcbcbc;line-height:1.5"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:10px;color:#666666;line-height:18px;padding-bottom:10px;width:100%;text-align:right;padding-right:10px">
                                                <tbody>
                                                    <tr>
                                                        <td>我们向您发送这封电子邮件通知，目的是让您了解本站相关的变化</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div style="direction: ltr;">©Copyright&nbsp;2019&nbsp;' .
      $blog_name .
      '</div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td width="32px"></td>
                    </tr>
                    <tr height="32px"></tr>
                </tbody>
            </table>
            <br>
        </body>
        </html>';
    wp_mail($comment_parent_author_email, $subject, $message, $headers);
  }
}
// add_action('wp_insert_comment', 'origami_comment_respond_email', 99, 2);

/**
 * 说说
 */
function origami_shuoshuo_init()
{
  $labels = array(
    'name' => '说说',
    'singular_name' => '说说',
    'add_new' => '发表说说',
    'add_new_item' => '发表说说',
    'edit_item' => '编辑说说',
    'new_item' => '新说说',
    'view_item' => '查看说说',
    'search_items' => '搜索说说',
    'not_found' => '暂无说说',
    'not_found_in_trash' => '没有已遗弃的说说',
    'parent_item_colon' => '',
    'menu_name' => '说说'
  );
  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'exclude_from_search' => true,
    'query_var' => true,
    'rewrite' => true,
    'capability_type' => 'post',
    'has_archive' => false,
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array('editor', 'author', 'title', 'custom-fields')
  );
  register_post_type('shuoshuo', $args);
}
add_action('init', 'origami_shuoshuo_init');

/**
 * 文末版权声明
 */
function origami_content_copyright($content)
{
  if (is_single() || is_feed()) {
    $content .=
      '<div id="content-copyright"><span style="font-weight:bold;text-shadow:0 1px 0 #ddd;font-size: 12px;">声明:</span><span style="font-size: 12px;">本文采用 <a rel="nofollow" href="http://creativecommons.org/licenses/by-nc-sa/3.0/" title="署名-非商业性使用-相同方式共享">BY-NC-SA</a> 协议进行授权，如无注明均为原创，转载请注明转自<a href="' .
      home_url() .
      '">' .
      get_bloginfo('name') .
      '</a><br>本文地址:<a rel="bookmark" title="' .
      get_the_title() .
      '" href="' .
      get_permalink() .
      '">' .
      get_the_title() .
      '</a></span></div>';
  }
  return $content;
}
add_filter('the_content', 'origami_content_copyright');

/**
 * 1.为标题添加id
 * 2.删除后台用来防止出现的问题pre块
 */
function origami_content_change($content)
{
  if (is_single() || is_feed()) {
    // 添加id
    $matches = array();
    $r = "/<h[1-3]>([^<]+)<\/h[1-3]>/im";
    if (preg_match_all($r, $content, $matches)) {
      foreach ($matches[1] as $num => $title) {
        $content = str_replace(
          $matches[0][$num],
          substr($matches[0][$num], 0, 3) .
            ' id="title-' .
            $num .
            '"' .
            substr($matches[0][$num], 3),
          $content
        );
      }
    }
    // 删除pre块
    $matches = array();
    $r = '/<pre class="fix-back-pre">([^<]+)<\/pre>/im';
    if (preg_match_all($r, $content, $matches)) {
      foreach ($matches[1] as $num => $con) {
        $content = str_replace($matches[0][$num], $con, $content);
      }
    }
  }
  return $content;
}
add_filter('the_content', 'origami_content_change');

/**
 * 短代码 - 处理
 */
function origami_shortcode_process($content)
{
  return do_shortcode($content);
}
add_filter('the_content', 'origami_shortcode_process');

/**
 * 短代码 - 添加
 */
function origami_prism_shortcode_func($attr, $content)
{
  $line = 'clike';
  $lang = '';
  if (isset($attr['lang']) && !empty($attr['lang'])) {
    $lang = $attr['lang'];
  }
  if (isset($attr['line-num']) && !empty($attr['line-num'])) {
    $line_num = $attr['line-num'];
  } else {
    $line_num = 'true';
  }
  if (strcmp($line_num, 'true') == 0) {
    $line = 'line-numbers ';
  }
  $output =
    '<pre class="' .
    $line .
    'language-' .
    $lang .
    '"><code class=" language-' .
    $lang .
    '">' .
    $content .
    '</code></pre>';
  return $output;
}
add_shortcode('prism', 'origami_prism_shortcode_func');

function origami_notebox_shortcode_func($attr, $content)
{
  $color = 'yellow';
  if (isset($attr['color']) && !empty($attr['color'])) {
    $color = $attr['color'];
  }
  $output =
    '<div class="message-box ' . $color . '"><p>' . $content . '</p></div>';
  return $output;
}
add_shortcode('notebox', 'origami_notebox_shortcode_func');

function origami_image_shortcode_func($attr, $content)
{
  $is_thum = 'false';
  $is_show = 'true';
  $alt = '';
  if (isset($attr['is-thum']) && !empty($attr['is-thum'])) {
    $is_thum = $attr['is-thum'];
  }
  if (
    isset($attr['is-show']) &&
    !empty($attr['is-show']) &&
    strcmp($is_thum, 'true') == 0
  ) {
    $is_show = $attr['is-show'];
  }
  if (isset($attr['alt']) && !empty($attr['alt'])) {
    $alt = $attr['alt'];
  }
  $src = $content;
  $output =
    '<img src="' . $src . '" alt="' . $alt . '" is-thum="' . $is_thum . '"';
  if (strcmp($is_show, 'false') == 0) {
    $output .= ' style="display:none"';
  }
  $output .= '>';
  return $output;
}
add_shortcode('image', 'origami_image_shortcode_func');

/**
 * 添加短代码按钮到文本编辑器
 */
function origami_add_html_button($mce_settings)
{
  ?>
    <script type="text/javascript">
        QTags.addButton('image_add', '添加图片', '[image alt="" is-thum="false" is-show="true"]', '[/image]');
        QTags.addButton('prism', 'Prism.js - 代码高亮', '<pre class="fix-back-pre">[prism lang=""]', '[/prism]</pre>');
        QTags.addButton('notebox_yellow', 'NoteBox - yellow', '[notebox color="yellow"]', '[/notebox]');
        QTags.addButton('notebox_blue', 'NoteBox - blue', '[notebox color=blue]', '[/notebox]');
        QTags.addButton('notebox_green', 'NoteBox - green', '[notebox color=green]', '[/notebox]');
        QTags.addButton('notebox_red', 'NoteBox - red', '[notebox color=red]', '[/notebox]');
    </script>
    <?php
}
add_action('after_wp_tiny_mce', 'origami_add_html_button');

function origami_register_button($buttons)
{
  array_push($buttons, " ", "origami_image_add");
  array_push($buttons, " ", "prism");
  array_push($buttons, " ", "notebox_yellow");
  array_push($buttons, " ", "notebox_blue");
  array_push($buttons, " ", "notebox_green");
  array_push($buttons, " ", "notebox_red");
  return $buttons;
}
function origami_add_plugin($plugin_array)
{
  $plugin_array['origami_image_add'] =
    get_template_directory_uri() . '/js/shortcode.js';
  $plugin_array['prism'] = get_template_directory_uri() . '/js/shortcode.js';
  $plugin_array['notebox_yellow'] =
    get_template_directory_uri() . '/js/shortcode.js';
  $plugin_array['notebox_blue'] =
    get_template_directory_uri() . '/js/shortcode.js';
  $plugin_array['notebox_green'] =
    get_template_directory_uri() . '/js/shortcode.js';
  $plugin_array['notebox_red'] =
    get_template_directory_uri() . '/js/shortcode.js';
  return $plugin_array;
}
add_filter('mce_external_plugins', 'origami_add_plugin');
add_filter('mce_buttons', 'origami_register_button');

/**
 * 设置文章缩略图
 */
function origami_get_other_thumbnail($post)
{
  // <img.+src=[\'"]([^\'"]+)[\'"].+is-thum=[\'"]([^\'"]+)[\'"].*>
  $image_url = false;
  preg_match_all(
    '/\[image.+is-thum="true".+\]([^\'"]+)\[\/image]/i',
    $post->post_content,
    $matches
  );
  if (isset($matches[1][0])) {
    $image_url = $matches[1][0];
  }
  return $image_url;
}

/**
 * Lazyload图片
 */
function origami_lazyload_img()
{
  $config = get_option('origami_lazyload');
  if (stripos($config, ',') == true) {
    $config = explode(',', $config);
  } else {
    $config = array('false');
  }
  if (strcmp($config[0], 'true') == 0) {
    if (strcmp($config[1], 'post') == 0) {
      add_filter('the_content', 'origami_lazyload_img_process');
    } else {
      add_action('template_redirect', 'lazyload_img_obstart');
      function lazyload_img_all($content)
      {
        return origami_lazyload_img_process($content);
      }
      ob_start('lazyload_img_all');
    }
  }
  function origami_lazyload_img_process_callback($matches)
  {
    $img_attr = $matches[1];
    // 替换单引号为双引号
    if (stripos($img_attr, "'") !== false) {
      $img_attr = str_replace("'", '"', $img_attr);
    }
    $img_class = '';
    if (stripos($img_attr, "src=") === false) {
      return $img_attr;
    } else {
      preg_match('/.*(src="([^"]*)?").*/i', $img_attr, $src_matches);
      $data_src = $src_matches[1];
      $img_attr = str_replace($data_src, 'src=""', $img_attr);
      if (
        stripos($img_attr, "width=") !== false ||
        stripos($img_attr, "width:") !== false ||
        stripos($img_attr, "height=") !== false ||
        stripos($img_attr, "height:") !== false
      ) {
        $load_src =
          'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';
        $img_attr = substr_replace(
          $img_attr,
          $load_src,
          stripos($img_attr, 'src=') + 5,
          0
        );
      } else {
        $load_src =
          'data:image/gif;base64,R0lGODlh+gD6AIAAAP///wAAACH5BAEAAAAALAAAAAD6APoAAAL/hI+py+0Po5y02ouz3rz7D4biSJbmiabqyrbuC8fyTNf2jef6zvf+DwwKh8Si8YhMKpfMpvMJjUqn1Kr1is1qt9yu9wsOi8fksvmMTqvX7Lb7DY/L5/S6/Y7P6/f8vv8PGCg4SFhoeIiYqLjI2Oj4CBkpOUlZaXmJmam5ydnp+QkaKjpKWmp6ipqqusra6voKGys7S1tre4ubq7vL2+v7CxwsPExcbHyMnKy8zNzs/AwdLT1NXW19jZ2tvc3d7f0NHi4+Tl5ufo6err7O3u7+Dh8vP09fb3+Pn6+/z9/v/w8woMCBBAsaPIgwocKFDBs6fAgxosSJFCtavIgxo8aNVhw7evwIMqTIkSRLmjyJMqXKlSxbunwJM6bMmTRr2ryJM6fOnTx7+vwJNKjQoUSLGj2KNKnSpUybOn0KNarUqVSrWr2KNavWrVy7ev0KNqzYsWTLXigAADs=';
        $img_attr = substr_replace(
          $img_attr,
          $load_src,
          stripos($img_attr, 'src=') + 5,
          0
        );
      }
      $img_attr = 'data-src="' . $src_matches[2] . '" ' . $img_attr;
    }
    if (stripos($img_attr, "class=") !== false) {
      $img_attr = substr_replace(
        $img_attr,
        'lazyload ',
        stripos($img_attr, 'class=') + 7,
        0
      );
    } else {
      $img_attr = 'class="lazyload" ' . $img_attr;
    }
    if (stripos($img_attr, "srcset=") !== false) {
      $img_attr = str_replace('srcset=', 'data-srcset=', $img_attr);
    }
    return '<img ' . $img_attr . '>';
  }
  function origami_lazyload_img_process($content)
  {
    $regex = "/<img (.+)>/i";
    $content = preg_replace_callback(
      $regex,
      "origami_lazyload_img_process_callback",
      $content
    );
    return $content;
  }
}
add_action('template_redirect', 'origami_lazyload_img');

/**
 * 实时搜索
 * ?action=real_time_search&search=...
 */
function origami_real_time_search()
{
  if (isset($_GET['action'])) {
    if ($_GET['action'] == 'real_time_search') {
      $query_args['s'] = $_GET['search'];
      $query = new WP_Query();
      $search_posts = $query->query($query_args);
      echo '<ul class="blog-post-list post-list row col-xlarge-12">';
      foreach ($search_posts as $post) {
        echo '<li class="col-xlarge-12"><div id="post-' .
          esc_attr($post->ID) .
          '" ';
        post_class(
          'post-list-item wide-post-list-item blog-list-item post-item-left' .
            $post->ID
        );
        echo 'style="padding-bottom:60px;">';
        echo '<h3 class="font-montserrat-reg"><a href="' .
          esc_url(get_the_permalink($post->ID)) .
          '">' .
          esc_attr(get_the_title($post->ID)) .
          '</a></h3>';
        echo '<div class="post-list-item-meta font-opensans-reg clearfix">';
        echo '<span>' .
          esc_attr(get_the_date(get_option('date_format'), $post->ID)) .
          '</span>';
        echo '<span>' .
          esc_attr(get_the_author_meta('nickname', $post->post_author)) .
          '</span>';
        echo '</div>';
        echo '<div class="page-content">' .
          wp_trim_words($post->post_content, 100) .
          '</div>';
        echo '<div class="myblog-post-list-buttom">';
        echo '<a href="' .
          esc_url(get_the_permalink($post->ID)) .
          '" class="primary-button font-montserrat-reg hov-bk myblog-post-list-button">Read more</a>';
        echo '</div></div></li>';
      }
      echo '</ul>';
      die();
    }
  }
}
add_action('init', 'origami_real_time_search');

// New Function.php
// 后台配置面板
require_once "include/config.class.php";
$config_class = new OrigamiConfig();

// 分页导航栏
function origami_pagination()
{
  global $wp_query;
  $big = 999999999;
  $pagination_args = [
    'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
    'format' => '?paged=%#%',
    'total' => $wp_query->max_num_pages,
    'current' => max(1, get_query_var('paged')),
    'show_all' => false,
    'end_size' => 1,
    'prev_next' => true,
    'prev_text' => '<i class="icon icon-back"></i> ' . __('上一页', 'origami'),
    'next_text' =>
      __('下一页', 'origami') . ' <i class="icon icon-forward"></i>',
    'type' => 'array',
    'add_args' => false,
    'add_fragment' => '',
    'before_page_number' => '',
    'after_page_number' => ''
  ];
  $page_arr = paginate_links($pagination_args);
  $paginate = '';
  foreach ($page_arr as $value) {
    $paginate .= '<li class="page-item">';
    $paginate .= $value;
    $paginate .= '</li>';
  }
  if ($paginate != '') {
    echo '<ul class="pagination">' . $paginate . '</ul>';
  }
}

//注册侧边栏
function origami_sidebar_init()
{
  register_sidebar([
    'name' => __('默认侧栏', 'origami'),
    'description' => '默认的侧边栏',
    'id' => 'default_sidebar',
    'before_widget' => '<aside class="sidebar-widget %2$s">',
    'after_widget' => '</aside>',
    'before_title' => '<h3>',
    'after_title' => '</h3>'
  ]);
}
add_action('widgets_init', 'origami_sidebar_init');

function comment_mark($comment)
{
  /* 评论者标签 - start */
  global $wpdb;
  $comment_mark = "";
  $comment_mark_color = "#CBBBBA";
  //站长邮箱
  $adminEmail = get_option('admin_email');
  //从数据库读取有人链接
  $linkurls = $wpdb->get_results("SELECT link_url FROM wp_links", "ARRAY_N");
  $other_friend_links = explode(',', get_option('origami_other_friends'));
  foreach ($other_friend_links as $other_friend_link) {
    $other_friend_links_arr[][0] = $other_friend_link;
  }
  $linkurls = array_merge($linkurls, $other_friend_links_arr);
  //默认不是朋友，将标记为访客
  $is_friend = false;
  //判断是不是站长我
  if ($comment->comment_author_email == $adminEmail) {
    $comment_mark =
      '<a target="_blank" href="/关于我" title="经鉴定，这货是站长">站长</a>';
    $comment_mark_color = "#0bf";
    $is_friend = true;
  }
  if (!$is_friend && $comment->comment_author_url != '') {
    $rex = '/(https:\/\/|http:\/\/)[a-z0-9-]*\.([a-z0-9-]+\.[a-z]+).*/i';
    $rex2 = '/(https:\/\/|http:\/\/)([a-z0-9-]+\.[a-z]+).*/i';
    if (substr_count($comment->comment_author_url, '.') == 2) {
      preg_match($rex, $comment->comment_author_url, $author_url_re);
    } else {
      preg_match($rex2, $comment->comment_author_url, $author_url_re);
    }
    $comment_author_url_reg = $author_url_re[2];
    foreach ($linkurls as $linkurl) {
      if (substr_count($linkurl[0], '.') == 2) {
        preg_match($rex, $linkurl[0], $url_re);
      } else {
        preg_match($rex2, $linkurl[0], $url_re);
      }
      if (
        $comment_author_url_reg != "" &&
        $comment_author_url_reg == $url_re[2]
      ) {
        $comment_mark =
          '<a target="_blank" href="/links" title="友情链接认证">友人</a>';
        $comment_mark_color = "#5EBED2";
        $is_friend = true;
      }
    }
  }
  //若不在列表中就标记为访客
  if ($is_friend == false) {
    $comment_mark = "访客";
  }
  return '<div class="comment-mark" style="background:' .
    $comment_mark_color .
    '">' .
    $comment_mark .
    '</div>';

  /* 评论者标签 - end */
}

// REST API 评论读取
function origami_rest_get_comments(WP_REST_Request $request)
{
  $post_id = $request['id'];
  $page_index = $request['page'] ? $request['page'] : 1;
  $pre_page = get_option('comments_per_page');
  $offset = (intval($page_index) - 1) * $pre_page;
  $parent = get_comments([
    "post_id" => $post_id,
    "number" => $pre_page,
    "offset" => $offset,
    "parent" => 0,
    "status" => "approve"
  ]);
  foreach ($parent as $item) {
    $item->comment_avatar = get_avatar(
      $item->comment_author_email,
      64,
      get_option("avatar_default"),
      "",
      [
        "class" => "comment-avatar"
      ]
    );
    $item->comment_mark = comment_mark($item);
    unset($item->comment_author_email);
    unset($item->comment_author_IP);
  }
  $stack = $parent;
  while (count($stack) != 0) {
    $tmp = array_pop($stack);
    $children = get_comments([
      "parent" => $tmp->comment_ID,
      "status" => "approve"
    ]);
    foreach ($children as $item) {
      $item->comment_avatar = get_avatar(
        $item->comment_author_email,
        64,
        get_option("avatar_default"),
        "",
        [
          "class" => "comment-avatar"
        ]
      );
      $item->comment_mark = comment_mark($item);
      unset($item->comment_author_email);
      unset($item->comment_author_IP);
    }
    $tmp->sub = $children;
    $stack = array_merge($stack, $children);
  }
  return $parent;
}
add_action('rest_api_init', function () {
  register_rest_route('origami/v1', '/comments', [
    'methods' => 'GET',
    'callback' => 'origami_rest_get_comments'
  ]);
});

// REST API 评论提交
function origami_rest_post_comments(WP_REST_Request $request)
{
  $comment_data = [
    "email" => $request["author_email"],
    "author" => $request["author_name"],
    "url" => $request["author_url"],
    "comment" => $request["content"],
    "comment_parent" => $request["parent"],
    "comment_post_ID" => $request["post"]
  ];
  $comment_re = wp_handle_comment_submission(wp_unslash($comment_data));
  if (is_wp_error($comment_re)) {
    $error = $comment_re->get_error_data();
    return [
      "code" => "wp_handle_comment_submission error",
      "data" => [
        "status" => $error
      ],
      "massage" => $comment_re->get_error_message()
    ];
  }
  $user = wp_get_current_user();
  do_action('set_comment_cookies', $comment_re, $user);
  $comment_re->comment_avatar = get_avatar(
    $comment_re->comment_author_email,
    64,
    get_option("avatar_default"),
    "",
    [
      "class" => "comment-avatar"
    ]
  );
  $comment_re->comment_mark = comment_mark($comment_re);
  unset($comment_re->comment_author_email);
  unset($comment_re->comment_author_IP);
  $aes = new Aes(
    get_option("origami_comment_key", "qwertyuiopasdfghjklzxcvbnm12345")
  );
  $change_token = $aes->encrypt(time() . ":" . $comment_re->comment_ID);
  setcookie("change_comment", $change_token, time() + 300, "/");
  return $comment_re;
}
add_action('rest_api_init', function () {
  register_rest_route('origami/v1', '/comments', [
    'methods' => 'POST',
    'callback' => 'origami_rest_post_comments'
  ]);
});

// REST API 修改评论 TODO: 调整是否开启
function origami_rest_put_comments(WP_REST_Request $request)
{
  $comment_data = [
    "comment_author_email" => $request["author_email"],
    "comment_author" => $request["author_name"],
    "comment_author_url" => $request["author_url"],
    "comment_content" => $request["content"],
    "comment_ID" => $request["id"],
    "comment_post_ID" => $request["post"]
  ];
  $error_401 = [
    "code" => "Insufficient permissions",
    "data" => [
      "status" => 401
    ],
    "massage" => "权限不足，未读取到合法的token"
  ];
  $error_403 = [
    "code" => "You cannot change comments over 5 minutes",
    "data" => [
      "status" => 403
    ],
    "massage" => "您无法更改超过5分钟的评论"
  ];
  $error_409 = [
    "code" => "Submitted comment ID does not match",
    "data" => [
      "status" => 409
    ],
    "massage" => "提交的评论ID不匹配"
  ];
  if (!isset($_COOKIE['change_comment'])) {
    return $error_401;
  }
  $aes = new Aes(
    get_option("origami_comment_key", "qwertyuiopasdfghjklzxcvbnm12345")
  );
  $data = explode(":", $aes->decrypt($_COOKIE['change_comment']));
  if (time() - $data[0] > 300) {
    return $error_403;
  }
  if ($comment_data['comment_ID'] != $data[1]) {
    return $error_409;
  }
  $status = wp_update_comment($comment_data);
  return $status;
}
add_action('rest_api_init', function () {
  register_rest_route('origami/v1', '/comments', [
    'methods' => 'PUT',
    'callback' => 'origami_rest_put_comments'
  ]);
});

// REST API 评论删除 TODO: 调整是否开启
function origami_rest_delete_comments(WP_REST_Request $request)
{
  $error_401 = [
    "code" => "Insufficient permissions",
    "data" => [
      "status" => 401
    ],
    "massage" => "权限不足，未读取到合法的token"
  ];
  $error_403 = [
    "code" => "You cannot change comments over 5 minutes",
    "data" => [
      "status" => 403
    ],
    "massage" => "您无法更改超过5分钟的评论"
  ];
  $error_409 = [
    "code" => "Comment ID does not found",
    "data" => [
      "status" => 400
    ],
    "massage" => "评论ID未找到"
  ];
  $comment_id = $request["id"];
  if (!isset($_COOKIE['change_comment'])) {
    return $error_401;
  }
  $aes = new Aes(
    get_option("origami_comment_key", "qwertyuiopasdfghjklzxcvbnm12345")
  );
  $data = explode(":", $aes->decrypt($_COOKIE['change_comment']));
  if (time() - $data[0] > 300) {
    return $error_403;
  }
  if (!$comment_id) {
    return $error_409;
  }
  $status = wp_delete_comment($comment_id);
  return $status;
}
add_action('rest_api_init', function () {
  register_rest_route('origami/v1', '/comments', [
    'methods' => 'DELETE',
    'callback' => 'origami_rest_delete_comments'
  ]);
});

class Aes
{
  protected $method;
  protected $secret_key;
  protected $iv;
  protected $options;
  public function __construct(
    $key,
    $method = 'AES-128-ECB',
    $iv = '',
    $options = 0
  ) {
    $this->secret_key = isset($key) ? $key : 'morefun';
    $this->method = $method;
    $this->iv = $iv;
    $this->options = $options;
  }
  public function encrypt($data)
  {
    return openssl_encrypt(
      $data,
      $this->method,
      $this->secret_key,
      $this->options,
      $this->iv
    );
  }
  public function decrypt($data)
  {
    return openssl_decrypt(
      $data,
      $this->method,
      $this->secret_key,
      $this->options,
      $this->iv
    );
  }
}
// end
