<?php
/**
 * @package BmobFileUpdate
 * @version 0.01
 */
/*
Plugin Name: LearCloudUpdate
Plugin URI:
Description:同步数据到LearnCloud数据库
Version: 0.1Beta
Author URI: http://sjmf.xyz
*/

// 如果是手动安装
require_once("lib/LeanCloud-sdk/autoload.php");

use LeanCloud\Object;
use LeanCloud\Query;
use LeanCloud\CloudException;

// 参数依次为 app-id, app-key, master-key
LeanCloud\Client::initialize("32Q4VUuEyOlpgbI3ftetyKUo-gzGzoHsz", "F96iFIDRbGKp5NODDQe03MCS", "FaMyBkrJGLL8a4ttsaPhEkP3");

function learncloud_data_update( $post_ID, $post_after, $post_before )
{
	try{
		
		  
		//获取文章基本信息
		$title   = get_post($id)->post_title;
		$postUrl = get_permalink($id);
		$version = "v3";
		$content = get_post($id)->post_content;
		$custom_fields = get_post($id)->custom_fields;
		$custom_fields =get_post_field('yunInfo',$id);
		$thumbnail=catch_that_image2( $id );
		  
		//查询LearCloud数据库是否同步
		$query = new Query("Movies02");
		$query->equalTo("PostID",$post_ID);
		$movies =$query->find();
		$query->limit(1); // 最多返回 1 条结果
		
		if(empty($movies)){
			//echo '没有查询到结果';
			
			$Movies1 = new Object("Movies02"); // 构建对象
			$Movies1->set("title",$title);           // 设置名称
			$Movies1->set("PostID", $post_ID); //文章id
			$Movies1->set("postUrl",$postUrl); //文章id
			$Movies1->set("thumbnail", $thumbnail); //文章id
			$Movies1->set("content", $content); //文章id
			$Movies1->set("yunUrl", $custom_fields); //文章id
			$Movies1->set("version", $version); //文章id


			try {
				$Movies1->save();
				// 保存到服务端
			} catch (CloudException $ex) {
				// 失败
			}
			
		    
			 
		}else{
			
			forEach($movies as $movie) {
				$objectId = $movie->getObjectId();
			}
			//echo $objectId;

			// 参数依次为 className、objectId
			$updateMovies = Object::create("Movies02",$objectId);

			// 修改
			$updateMovies->set("title",$title);           // 设置名称
			$updateMovies->set("PostID", $post_ID); //文章id
			$updateMovies->set("postUrl",$postUrl); //文章id
			$updateMovies->set("thumbnail", $thumbnail); //文章id
			$updateMovies->set("content", $content); //文章id
			$updateMovies->set("yunUrl", $custom_fields); //文章id
			$updateMovies->set("version", $version); //文章id
			
			// 保存到云端
			$updateMovies->save();			
					 
		}
		 

	
	
		
	}catch (Exception $e) {
		echo $e;
		myDebug( $e);
    }
  
}
function getAttachments_Url($id){
	$attachments = get_attached_media( 'application/octet-stream', $id );
	$at=array_values($attachments);
	$fileName=$at[0]->post_title;
	$fileUrl=$at[0]->guid;
	$fileID=$at[0]->ID;
	return $fileUrl;
}

function catch_that_image2( $id ) {
	global $post, $posts;
	$first_img = '';
	// 如果设置了缩略图
	$post_thumbnail_id = get_post_thumbnail_id( $id );
	if ( $post_thumbnail_id) {
		$output = wp_get_attachment_image_src( $post_thumbnail_id, 'large' );
		$first_img = $output[0];
		
	}else { // 没有缩略图，查找文章中的第一幅图片
	
	    ob_start();
        ob_end_clean();
        $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
        $first_img = $matches [1] [0];
        if(empty($first_img)){ //Defines a default image
			$first_img = "http://p1.bqimg.com/1949/2504b4b87d0517a3.png";
        }
        return $first_img;
	}
	 
	return $first_img;
}


/* 挂载到post_updated*/
add_action('post_updated', 'learncloud_data_update', 10 , 3);


//debug
function myDebug($content){
	
	$of = fopen('Debug.txt','w');//创建并打开Debug.txt
    if($of){
        fwrite($of,$content);//把执行文件的结果写入txt文件
    }
	fclose($of);
	
	
}



?>
