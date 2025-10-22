<?php
class Blog{

	const UPLOAD_FOLDER = 'images/blog';
	const ALLOWED_EXTENSION = ['jpeg','jpg','png'];

	public function index($slug = null)
	{
		if (is_null($slug)) 
		{
			$home = new GeneralesModel("home");
			$contact = new FrontModel("contact");
			$blog = new BlogModel();
			$data["blogs"] = $blog->allWithOffsetFront(0);
			$data['blog_number'] = ceil(count($blog->all()) / 6) ;
			$data['blog_page'] = 1 ;

			$category = new CategoryBlogModel();
			$categorys = $category->getAll();
	
			foreach ($categorys as $key => $cat) 
			{
				$blogCategory = $blog->filterAllByCategory($cat->category);
				$data['categorys'][$cat->category] = count($blogCategory);
			}
			$data['contact'] = $contact->getAll();
			$data['home'] = $home;
			$data['pageBlog'] = true;
			$data['headers'] = [0 => ['Blog' => BASE_URL.'/#blog']];
			$data['countBlog'] = count($blog->all());
			Controllers::loadView('category.php',$data);
		}
		else
		{
			$slug = urldecode($slug);
	
			$db = new Database();
			
			$res = $db->select("blog")
				->where("slug_blog", "=")
				->execute([$slug]);
	
			if (count($res) == 0) {
				header("location:" . BASE_URL);
			} else {
				// on vérifie que l'id existe
				$contact = new FrontModel("contact");
				$blog = new BlogModel();
				$front = new FrontModel('title');
				$comment = new CommentModel();
				$data['title'] = $front->getAllTitle()[0];
				$data['blogs'] = $blog->getBlogWithLimite($res[0]->id_blog);
				$data['contact'] = $contact->getAll();
				$data['blog'] = $blog->getOne($res[0]->id_blog);
	
				if(isset($_POST) && !empty($_POST) ){
					$validation = new FormValidation();
					$validation->required('comment');
					$validation->required('name');
					$validation->required('mail');
					$validation->required('contact');
					if(!$validation->run())
					{
						$data['error'] = "Tous les champs sont requis.";
						$data['error_list'] = $_POST;
					}else{
	
						$validation->email(htmlspecialchars(trim($_POST['mail'])));
						if(!$validation->run())
						{
							$data['error'] = "Cette adresse email n'est pas valide!";
							$data['error_list'] = $_POST;
						}else{
	
							$fields = [];
							$values = [];
							foreach ($_POST as $key => $post) {
								$fields[] = htmlspecialchars(trim($key));
								if($key == 'id_blog'){
									$values[] = (int)$post;
								}else{
									$values[] = htmlspecialchars(trim($post));
								}
							}
							$comment->create($fields,$values);
							$data['success'] = "Votre commentaire à été envoyer avec success.";
	
						}
					}
				}
	
				$data['comments'] = $comment->getAllComment($res[0]->id_blog);
				Controllers::loadView("blogAccueil.php", $data);
			}
		}
	}

	public function create()
	{
		$validation = new FormValidation();
		$fileController = new FileController($_FILES,self::UPLOAD_FOLDER);
		$validation->required('title');
		$validation->required('content');
		$validation->required('blog_category');
		if(!$validation->run())
		{
			echo json_encode($validation->getErrors());
			exit();
		}

		if($_FILES['image']['name'] !== '')
		{
			$fileController->verifyExtension(self::ALLOWED_EXTENSION);
			if(!empty($fileController->getErrors()))
			{
				echo json_encode($fileController->getErrors());
				exit();
			}
			$fileController->upload($_FILES['image']['name']);
		}else
		{
			$data['error'] = 'Une image est requis';
			echo json_encode($data);
			exit();
		}
		
		$model = new BlogModel();
		$title = htmlspecialchars(trim($_POST['title']));
		$content = trim($_POST['content']);
		$image =  $_FILES['image']['name'];
		$date = htmlspecialchars(trim($_POST['date']));
		$ifram = htmlspecialchars(trim($_POST['ifram']));
		$title = htmlspecialchars(trim($_POST['title']));
		$blog_category = htmlspecialchars(trim($_POST['blog_category']));
		
		$slug = Utility::formatUrl($title);

		if($date !== ''){
			$model->create(["title_blog", "content_blog", "image_blog", "creat_at_blog", "ifram_blog", "blog_category", "slug_blog"],[$title, $content, $image, $date, $ifram, $blog_category, $slug]);
		}else{
			$model->create(["title_blog", "content_blog", "image_blog", "ifram_blog", "blog_category", "slug_blog"],[$title, $content, $image, $ifram, $blog_category, $slug]);
		}

       //$data['category'] = $model->getLast();
		$data['success'] = true;
		echo json_encode($data);
	}

	public function update()
	{
		$validation = new FormValidation();
		$fileController = new FileController($_FILES,self::UPLOAD_FOLDER);
		$validation->required('title');
		$validation->required('content');
		$validation->required('blog_category');
		if(!$validation->run())
		{
			echo json_encode($validation->getErrors());
			exit();
		}

		$issetImage = false;
		if($_FILES['image']['name'] !== '')
		{
			$issetImage = true;
			$fileController->verifyExtension(self::ALLOWED_EXTENSION);
			if(!empty($fileController->getErrors()))
			{
				echo json_encode($fileController->getErrors());
				exit();
			}
			$fileController->upload($_FILES['image']['name']);
		}
		$model = new BlogModel();
		$title = htmlspecialchars(trim($_POST['title']));
		$content = trim($_POST['content']);
		$date = htmlspecialchars(trim($_POST['date']));
		$ifram = htmlspecialchars(trim($_POST['ifram']));
		$title = htmlspecialchars(trim($_POST['title']));
		$blog_category = htmlspecialchars(trim($_POST['blog_category']));
		$slug = Utility::formatUrl($title);
		$id = (int)$_POST['id'];

		if($issetImage) {
			$image =  $_FILES['image']['name'];
			if($date !== ''){
				$model->update(["title_blog", "content_blog", "image_blog", "creat_at_blog", "ifram_blog", "blog_category", "slug_blog"],[$title, $content, $image, $date, $ifram, $blog_category, $slug], $id);
			}else{
				$model->update(["title_blog", "content_blog", "image_blog", "ifram_blog", "blog_category", "slug_blog"],[$title, $content, $image, $ifram, $blog_category, $slug], $id);
			}
		}else{
			if($date !== ''){
				$model->update(["title_blog", "content_blog", "creat_at_blog", "ifram_blog", "blog_category", "slug_blog"],[$title, $content, $date, $ifram, $blog_category, $slug], $id);
			}else{
				$model->update(["title_blog", "content_blog", "ifram_blog", "blog_category", "slug_blog"],[$title, $content, $ifram, $blog_category, $slug], $id);
			}
		}


       //$data['category'] = $model->getLast();
		$data['success'] = true;
		echo json_encode($data);
	}

	public function getAll()
	{
		$model = new BlogModel();
		$data['blogs'] = $model->all();
		Controllers::loadView("tableBlog.php",$data);
	}

	public function getOne() 
	{
		$model = new BlogModel();
		$data = $model->getOne((int)$_POST['id']);
		echo json_encode($data);
	}

	public function delete()
	{
		$model = new BlogModel();
		$model->delete((int)$_POST['id']);
		echo json_encode(['success'=>true]);
	}

	public function page($i) 
	{
		$offset = ($i - 1)*6;
		
		$home = new GeneralesModel("home");
		$contact = new FrontModel("contact");
		$category = new CategoryBlogModel();
		$blog = new BlogModel();
		$data['blogs'] = $blog->allWithOffsetFront($offset);
		$data['blog_number'] = ceil(count($blog->all()) / 6) ;
		$data['blog_page'] = $i ;

		$data['contact'] = $contact->getAll();
		$data['home'] = $home;
		$data['pageBlog'] = true;
		$data['headers'] = [0 => ['Blog' => BASE_URL.'/#blog']];
		
		$categorys = $category->getAll();

		foreach ($categorys as $key => $cat) 
		{
		$blogCategory = $blog->filterAllByCategory($cat->category);
		$data['categorys'][$cat->category] = count($blogCategory);
		}

		$data['countBlog'] = count($blog->all());
		Controllers::loadView('category.php',$data);
	}

	public function search()
	{
		$query = htmlspecialchars(trim($_POST['query']));
		$model = new BlogModel();
		$data['blogs'] = $model->recherche($query);
		Controllers::loadView("tableBlog.php",$data);
	}

	public function getComments() 
	{
		$comment = new CommentModel();
		$data['comments'] = $comment->getAllCommentBack((int)$_POST['id']);
		Controllers::loadView("tableComments.php",$data);
	} 

	public function validateComments() 
	{
		$comment = new CommentModel();
		$comment->validateComment((int)$_POST['id_comment']);
		$data['comments'] = $comment->getAllCommentBack((int)$_POST['id_blog']);
		Controllers::loadView("tableComments.php",$data);
	} 

	public function deleteComments() 
	{
		$comment = new CommentModel();
		$comment->deleteComments((int)$_POST['id_comment']);
		$data['comments'] = $comment->getAllCommentBack((int)$_POST['id_blog']);
		Controllers::loadView("tableComments.php",$data);
	} 

	public function seeMoreComments()
	{
		$comment = new CommentModel();
		$data = $comment->seeMoreComments((int)$_POST['id_comment']);
		echo json_encode($data);
	}

}