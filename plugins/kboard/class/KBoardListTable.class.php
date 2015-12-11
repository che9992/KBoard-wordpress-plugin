<?php
/**
 * KBoard 게시판 리스트 테이블
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBoardListTable extends WP_List_Table {
	
	var $board;
	var $list;
	
	public function __construct(){
		parent::__construct();
		
		$this->board = new KBoard();
		$this->list = new KBoardList();
	}
	
	public function prepare_items(){
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		$keyword = isset($_GET['s'])?$_GET['s']:'';
		
		$this->list->rpp = 20;
		$this->list->page = $this->get_pagenum();
		$this->list->initWithKeyword($keyword);
		$this->items = $this->list->resource;
		
		$this->set_pagination_args(array('total_items'=>$this->list->total, 'per_page'=>$this->list->rpp));
	}
	
	public function no_items(){
		echo '게시판이 없습니다.';
	}
	
	public function get_columns(){
		return array(
				'cb' => '<input type="checkbox">',
				'thumbnail' => '미리보기',
				'board_name' => '게시판 이름',
				'auto_page' => '설치된 페이지',
				'skin' => '스킨',
				'permission_read' => '읽기권한',
				'permission_write' => '쓰기권한',
				'permission_comments_write' => '댓글쓰기권한',
				'date' => '생성일자'
		);
	}
	
	function get_bulk_actions(){
		return array(
				'delete' => '삭제'
		);
	}
	
	public function display_rows(){
		foreach($this->items as $item){
			$this->board->initWithRow($item);
			$this->single_row($this->board);
		}
	}
	
	public function single_row($item){
		$edit_url = admin_url("admin.php?page=kboard_list&board_id={$item->uid}");
		
		echo '<tr data-board-id="'.$item->uid.'">';
		
		echo '<th scope="row" class="check-column">';
		echo '<input type="checkbox" name="board_id[]" value="'.$item->uid.'">';
		echo '</th>';
		
		echo '<td><a href="'.$edit_url.'" title="편집">';
		echo '<img src="'.KBOARD_URL_PATH."/skin/{$item->skin}/thumbnail.png".'" style="width:100px;height:100px;" alt="">';
		echo '</a></td>';
		
		echo '<td><a href="'.$edit_url.'" title="편집">';
		echo $item->board_name;
		echo '</a></td>';
		
		echo '<td>';
		if($item->meta->auto_page){
			$post = get_post($item->meta->auto_page);
			echo '<a href="'.get_permalink($post).'" title="페이지 보기">';
			echo $post->post_title;
			echo '</a>';
		}
		else{
			echo '페이지 연결 없음';
		}
		echo '</td>';
		
		echo '<td>';
		echo $item->skin;
		echo '</td>';
		
		echo '<td>';
		echo kboard_permission($item->permission_read);
		echo '</td>';
		
		echo '<td>';
		echo kboard_permission($item->permission_write);
		echo '</td>';
		
		echo '<td>';
		if(!$item->meta->permission_comment_write){
			echo kboard_permission('all');
		}
		else if($item->meta->permission_comment_write == 1){
			echo kboard_permission('author');
		}
		else if($item->meta->permission_comment_write == 'roles'){
			echo kboard_permission('roles');
		}
		echo '</td>';
		
		echo '<td>';
		echo date('Y-m-d H:i:s', strtotime($item->created));
		echo '</td>';
		
		echo '</tr>';
	}
	
	public function search_box($text, $input_id){
	?>
	<p class="search-box">
		<input type="search" id="<?php echo $input_id?>" name="s" value="<?php _admin_search_query()?>">
		<?php submit_button($text, 'button', false, false, array('id'=>'search-submit'))?>
	</p>
	<?php }
}
?>