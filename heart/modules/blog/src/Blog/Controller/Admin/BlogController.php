<?php

namespace Blog\Controller\Admin;

use Blog\Model\Blog;

class BlogController extends \AdminController
{
	public function before()
	{
		$this->menu->activeParent('content');

		$this->template->style('blog.css','blog');
		$this->template->script('blog.js','blog');

		$ajax = $this->request->isAjax();

		if ($ajax) {
			$this->template->partialOnly();
		}
	}

	/**
	 * Blog Index
	 *
	 * @return void
	 **/
	public function index($id = null)
	{
		//Pagination
		$options = array(
		    'total_items'       => Blog::count(),
		    'items_per_page'    => \Setting::get('admin_item_per_page'),
		);

		$pagination = \Pagination::create($options);

		$blogs = Blog::with(array('category','author'))
							->orderBy('created_at', 'desc')
							->skip(\Pagination::offset())
							->take(\Pagination::limit())
							->get();

		$this->template->title(t('blog::blog.title_main'))
						->setPartial('admin/index')
						->set('pagination', $pagination)
					    ->set('blogs',$blogs);

		$data_table = $this->template->partialRender('admin/table');
		$this->template->set('data_table', $data_table);
	}

	/**
	 * Blog Create
	 *
	 * @return void
	 **/
	public function create()
	{
		if (!user_has_access('blog.create')) {
				return $this->notFound();
		}

		$blog = new Blog();
		
		if (\Input::isPost()) {

			if (\Input::get('id') != '') {
				$blog = self::setValues('edit', \Input::get('id'));
			} else {
				$blog = self::setValues('create');
			}

			if ($blog->save()) {

				self::tagSave($blog->id);

				if (\Module::isEnabled('field')) {

					\Field::save('blog', $blog);
						
				}

				\Event::call('reborn.blog.create');
				\Flash::success(t('blog::blog.create_success'));
				return \Redirect::to(adminUrl('blog'));

			} else {

				\Flash::error(t('blog::blog.create_error'));

			}

		}

		self::formEle($blog);

		$this->template->title(t('blog::blog.title_create'))
						->set('method', 'create');
	}

	/**
	 * Edit Blog
	 *
	 * @return void
	 **/
	public function edit($id = null)
	{
		$blog = Blog::find($id);
		
		if (\Input::isPost()) {
			
				$blog = self::setValues('edit', \Input::get('id'));

				if ($blog->save()) {

					self::tagSave($blog->id);

					if (\Module::isEnabled('field')) {

						\Field::update('blog', $blog);
							
					}

					\Flash::success(t('blog::blog.edit_success'));

					return \Redirect::to(adminUrl('blog'));

				} else {

					\Flash::error(t('blog::blog.edit_error'));

				}
			
		} else {

			if ($id == null) {

				return \Redirect::to(adminUrl('blog'));

			}

		}

		self::formEle($blog);
		
		$this->template->title('Edit Blog')
					   	->set('method', 'edit');
	}

	/**
	 * Change Blog Status
	 *
	 * @return void
	 **/
	public function changeStatus($id = null)
	{
		if (!$id) {
			return $this->notFound();
		}
		$blog = Blog::find($id);
		if ($blog->status == 'draft') {
			$blog->status = 'live';
		} else {
			$blog->status = 'draft';
		}
		$save = $blog->save(array(), false);
		if ($save) {
			\Flash::success(t('blog::blog.change_status_success'));
		} else {
			\Flash::error(t('blog::blog.change_status_error'));
		}
		return \Redirect::to(adminUrl('blog'));
	}

	/**
	 * Delete Blog
	 *
	 * @return void
	 **/
	public function delete($id = 0)
	{
		$ids = ($id) ? array($id) : \Input::get('action_to');

		$blogs = array();

		foreach ($ids as $id) {
			if ($blog = Blog::find($id)) {
				if (\Module::isEnabled('field')) {
					\Field::delete('blog', $blog);
				}
				$blog->delete();
				if (\Module::isEnabled('tag')) {
					$tag = \Tag\Model\TagsRelationship::where('object_id', $id)
													->where('object_name', 'blog')
													->delete();
				}
				if (\Module::isEnabled('comment')) {
					$comment_delete = \Comment\Lib\Helper::commentDelete($id, 'blog');
				}
				$blogs[] = "success";
			}
		}

		if (!empty($blogs)) {
			if (count($blogs) == 1) {
				\Flash::success(t('blog::blog.delete_success'));
			} else {
				\Flash::success(t('blog::blog.delete_success_many'));
			}
			\Event::call('reborn.blog.delete');
		} else {
			\Flash::error(t('blog::blog.delete_error'));
		}
		return \Redirect::to(adminUrl('blog'));
	}

	/**
	 * Set JS and Style to Template
	 *
	 * @return void
	 **/
	protected function formEle($blog)
	{
		$fields = array();
		if (\Module::isEnabled('field')) {
			$fields = \Field::getForm('blog', $blog);
		}
		$authors[0] = '-- '. t('blog::blog.auto_detect') .' -- ';
		$users = \Sentry::getUserProvider()->findAllWithAccess('admin');
		foreach ($users as $user) {
			$authors[$user->id] = $user->first_name . ' ' . $user->last_name;
		}
		$this->template->setPartial('admin/form')
						->set('authors', $authors)
						->set('blog', $blog)
						->set('custom_field', $fields)
						->style(array(
							'plugins/jquery.tagsinput_custom.css',
							'form.css'))
					   	->script(array(
						 	'plugins/jquery-ui-timepicker-addon.js',
						 	'plugins/jquery.tagsinput.min.js',
						 	'form.js'));
	}

	/**
	 * Set Form Values of Create and Edit Blog
	 *
	 * @return boolean
	 **/
	protected function setValues($method, $id = null) {
		if ($method == 'create') {
			$blog = new Blog;
		} else {
			$blog = Blog::find($id);
		}

		if (\Input::get('author_id') == 0) {
			$author = \Sentry::getUser()->id;
		} else {
			$author = \Input::get('author_id');
		}

		if (\Input::get('publish') != null) {
			$status = 'live';
		} else if (\Input::get('save_draft') != null) {
			$status = 'draft';
		} else {
			$status = null;
		}

		//if excerpt is empty get some part from body
		if (\Input::get('excerpt') == '') {
			$excerpt = self::getExcerpt(\Input::get('body'), 50);
		} else {
			$excerpt = \Input::get('excerpt');
		}

		$slug = (\Input::get('slug') == '') ? 'untitled' : \Input::get('slug');

		$id = \Input::get('id');

		$slug_check = self::slugDuplicateCheck($slug, $id);

		if ($slug_check) {
			$n = 1;
			do {
				$match = preg_match('/(.+)_([0-9]+)$/', $slug, $matches);
				if ($match) {
					$slug = $matches[1].'_'.$n;
				} else {
					$slug = $slug.'_'.$n;
				}
				$check = self::slugDuplicateCheck($slug, $id);
				$n++;
			} while ($check);
		}

		$blog->title = (\Input::get('title') == '') ? 'Untitled' : \Input::get('title');
		$blog->slug = $slug;
		$blog->category_id = \Input::get('category_id');
		$blog->excerpt = $excerpt;
		$blog->body = \Input::get('body');
		$blog->author_id = $author;
		$blog->comment_status = \Input::get('comment_status');
		if ($status != null) {
			$blog->status = $status;
		}
		if (\Input::get('sch_type') != null) {
			if (\Input::get('sch_type') == 'manual') {
				$blog->created_at = new \DateTime(\Input::get('date'));
			} else {
				if ($method == 'create') {
					$blog->created_at = new \DateTime();
				}
			}
		}

		if ($method == 'edit') {
			$blog->updated_at = new \DateTime();
		}
		
		// Remove Base Url from Attachment
		$blog->attachment = remove_base_url(\Input::get('attachment'));
		//type

		return $blog;

	}

	/**
	 * Save Blog Tags
	 *
	 * @return boolean
	 **/
	protected function tagSave($id)
	{
		\Module::load('Tag');
		$tag = \Tag\Controller\Admin\TagController::import($id, 'blog', \Input::get('blog_tag'));

		if ($tag) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Ajax Check slug
	 *
	 * @return void
	 **/
	public function checkSlug()
	{
	    $slug = \Input::get('slug');
	    if ($slug == "") {
	        return "*** This Field is required.";
	    } else {
	        $id = (int)\Input::get('id');
	        if ($id != '') {
	            //page edit check slug
	            $data = Blog::where('slug', '=', $slug)->where('id', '!=', $id)->get();
	        } else {
	            //page create check slug
	            $data = Blog::where('slug', '=', $slug)->get();
	        }
	        if (count($data) > 0) {
	            $error_msg = t('validation.slug_duplicate');

	            return $error_msg;
	        }
	    }
	    $this->template->partialOnly();
	}

	protected function slugDuplicateCheck($slug, $id) {
		$check = Blog::where('slug', $slug)
					->where('id', '!=', $id)
					->get();
		if (count($check)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Create excerpt if excerpt field is blank
	 *
	 * @return void
	 **/
	protected function getExcerpt($text, $limit = 50, $etc_str = "...")
	{
		$h = html_entity_decode($text);
		$e = strip_tags($h);
		if (str_word_count($e) > $limit) {
			$exp = explode(' ', $e, $limit+1);
			array_pop($exp);
			$excerpt = implode(' ', $exp).' '. $etc_str;
		} else {
			$excerpt = $e;
		}

		return $excerpt;
	}

	/**
	 * Ajax Filter Search
	 *
	 * @return void
	 **/
	public function search()
	{
		$term = \Input::get('term');
		if ($term) {
			$result = Blog::with(array('category','author'))
						->where('title', 'like', '%'.$term.'%')
						->get();
		} else {
			$options = array(
			    'total_items'       => Blog::count(),
			    'items_per_page'    => \Setting::get('admin_item_per_page'),
			);

			$pagination = \Pagination::create($options);

			$result = Blog::with(array('category','author'))
								->skip(\Pagination::offset())
								->take(\Pagination::limit())
								->get();
			$this->template->set('pagination', $pagination);
		}


		$this->template->partialOnly()
			 ->set('blogs', $result)
			 ->setPartial('admin/table');
	}

	/**
	 * Autosave Posts
	 *
	 * @return json
	 **/
	public function autosave()
	{
		$ajax = $this->request->isAjax();
		if ($ajax) {
			if (\Input::isPost()) {
				if ((\Input::get('title') == '' ) and (\Input::get('slug') == '') and (\Input::get('body') == ''))  {
					return $this->returnJson(array('status' => 'no_save'));
				} else {
					if (\Input::get('id') == '') {
						$blog = self::setValues('create');
					} else {
						// update
						$blog = self::setValues('edit', \Input::get('id'));
					}

					if ($blog->save()) {
						return $this->returnJson(array('status' => 'save', 'post_id' => $blog->id, 'time' => sprintf(t('blog::blog.autosave_on'), date('d - M - Y H:i A', time()))));
					}

				}
			}
		}
		return \Redirect::to(adminUrl('blog'));
	}

	public function after($response)
	{
		return parent::after($response);
	}
}
