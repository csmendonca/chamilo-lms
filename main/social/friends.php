<?php
/* For licensing terms, see /license.txt */

/**
 * @package chamilo.social
 * @author Julio Montoya <gugli100@gmail.com>
 */

$cidReset = true;
require_once '../inc/global.inc.php';

api_block_anonymous_users();
if (api_get_setting('allow_social_tool') != 'true') {
    api_not_allowed();
}

$this_section = SECTION_SOCIAL;

$htmlHeadXtra[] = '<script>

function delete_friend (element_div) {
	id_image=$(element_div).attr("id");
	user_id=id_image.split("_");
	if (confirm("'.get_lang('Delete', '').'")) {
		 $.ajax({
			contentType: "application/x-www-form-urlencoded",
			type: "POST",
			url: "'.api_get_path(WEB_AJAX_PATH).'social.ajax.php?a=delete_friend",
			data: "delete_friend_id="+user_id[1],
			success: function(datos) {
			 $("div#"+"div_"+user_id[1]).hide("slow");
			 $("div#"+"div_"+user_id[1]).html("");
			 clear_form ();
			}
		});
	}
}


function search_image_social()  {
	var name_search = $("#id_search_image").attr("value");
	 $.ajax({
		contentType: "application/x-www-form-urlencoded",
		type: "POST",
		url: "'.api_get_path(WEB_AJAX_PATH).'social.ajax.php?a=show_my_friends",
		data: "search_name_q="+name_search,
		success: function(data) {
			$("#friends").html(data);
		}
	});
}

function show_icon_delete(element_html) {
	elem_id=$(element_html).attr("id");
	id_elem=elem_id.split("_");
	ident="#img_"+id_elem[1];
	$(ident).attr("src","../img/delete.png");
	$(ident).attr("alt","'.get_lang('Delete', '').'");
	$(ident).attr("title","'.get_lang('Delete', '').'");
}


function hide_icon_delete(element_html)  {
	elem_id=$(element_html).attr("id");
	id_elem=elem_id.split("_");
	ident="#img_"+id_elem[1];
	$(ident).attr("src","../img/blank.gif");
	$(ident).attr("alt","");
	$(ident).attr("title","");
}

function clear_form () {
	$("input[@type=radio]").attr("checked", false);
	$("div#div_qualify_image").html("");
	$("div#div_info_user").html("");
}

</script>';

$interbreadcrumb[] = array('url' => 'profile.php', 'name' => get_lang('SocialNetwork'));
$interbreadcrumb[] = array('url' => '#', 'name' => get_lang('Friends'));

//Block Social Menu
$social_menu_block = SocialManager::show_social_menu('friends');

$user_id = api_get_user_id();

$user_id = api_get_user_id();
$name_search = isset($_POST['search_name_q']) ? $_POST['search_name_q'] : null;
$number_friends = 0;

if (isset($name_search) && $name_search != 'undefined') {
    $friends = SocialManager::get_friends($user_id, null, $name_search);
} else {
    $friends = SocialManager::get_friends($user_id);
}

$social_right_content = '<div class="col-md-9">';

if (count($friends) == 0) {
    $social_right_content .= get_lang('NoFriendsInYourContactList').'<br /><br />';
    $social_right_content .= '<a class="btn btn-primary" href="search.php">
								<i class="fa fa-search"></i>'.get_lang('TryAndFindSomeFriends').'</a>';
} else {
    $social_right_content .= get_lang('Search').'&nbsp;&nbsp; : &nbsp;&nbsp;';
    $social_right_content .= '<input class="social-search-image" type="text" id="id_search_image" name="id_search_image" onkeyup="search_image_social()" />';

    $friend_html = '<div id="friends">';

    $number_friends = count($friends);
    $j = 0;

    for ($k = 0; $k < $number_friends; $k++) {
        while ($j < $number_friends) {
            if (isset($friends[$j])) {
                $friend_html.='<div class="card-social">';
                $friend = $friends[$j];
                $user_name = api_xml_http_response_encode($friend['firstName'].' '.$friend['lastName']);
				$userPicture = UserManager::getUserPicture($friend['friend_user_id']);
                $friend_html.='<div class="avatar" class="image-social-content" id=div_'.$friends[$j]['friend_user_id'].'>';
                $friend_html.='<img src="'.$userPicture.'" id="imgfriend_'.$friend['friend_user_id'].'" title="'.$user_name.'" />                                    ';
				$friend_html.='</div>';

                $friend_html.='<div class="content">
                               <a href="profile.php?u='.$friend['friend_user_id'].'"> <h5>'.$user_name.'</h5></a>';
                $friend_html.='<p><button onclick="delete_friend(this)" id=img_'.$friend['friend_user_id'].'  />'.get_lang('Delete').'</button></p>
                        		</div>';
                $friend_html.='</div>';
            }
            $j++;
        }
    }
    $friend_html.='</div>';
    $social_right_content .= $friend_html;
}
$social_right_content .= '</div>';

$tpl = new Template(get_lang('Social'));
SocialManager::setSocialUserBlock($tpl, $user_id, 'friends');

$tpl->assign('social_menu_block', $social_menu_block);
$tpl->assign('social_right_content', $social_right_content);

$social_layout = $tpl->get_template('social/friends.tpl');
$tpl->display($social_layout);
