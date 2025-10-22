<?php
class Category{

  public function index($slug = null, $text = null, $page = null)
  {
		$home = new GeneralesModel("home");
    $contact = new FrontModel("contact");
    $data['contact'] = $contact->getAll();
		$data['home'] = $home;
    $data['pageBlog'] = true;

    $blog = new BlogModel();

		$category = new CategoryBlogModel();
		$categorys = $category->getAll();

    foreach ($categorys as $key => $cat) 
    {
      $blogCategory = $blog->filterAllByCategory($cat->category);
      $data['categorys'][$cat->category] = count($blogCategory);
    }

    if (is_null($slug))
    {
      $data["blogs"] = $blog->allWithOffsetFront(0);
      $data['blog_number'] = ceil(count($blog->all()) / 6) ;
      $data['blog_page'] = 1 ;
      $data['isCategory'] = true;
      
      $data['headers'] = [0 => ['Blog' => BASE_URL.'/#blog'], 
                          1 => ['Categorie' => BASE_URL.'/category'] ];

    }
    else{
      $offset = 0;
      $i = 1;

      if (isset($text)) {
        $i = $page;
        $offset = ($i - 1) * 6;
      }

      $title = urldecode($slug);
      $title = str_replace("-", " ", $title);

      $data["blogs"] = $blog->filterByCategoryWithOffset($title, $offset);
      $data['blog_number'] = ceil(count($blog->filterAllByCategory($title)) / 6) ;
      $data['blog_page'] = $i ;
      $data['isCategory'] = true;
      $data['categorySelected'] = $slug;

      if (empty($blogs)) {
        $data['headers'] = [0 => ['Blog' => BASE_URL.'/#blog'], 
                            1 => ['Categorie' => BASE_URL.'/category'],
                            2 => [$category->getWithName($title)->category => BASE_URL.'/category/'.$slug] ];
      }
      else{
        $data['headers'] = [0 => ['Blog' => BASE_URL.'/#blog'], 
                            1 => ['Categorie' => BASE_URL.'/category'],
                            2 => [$data["blogs"][0]->category => BASE_URL.'/category/'.$slug] ];
      }
    }
    
		$data['countBlog'] = count($blog->all());
    Controllers::loadView('category.php',$data);
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
    $data['isCategory'] = true;

		$data['contact'] = $contact->getAll();
		$data['home'] = $home;
		$data['pageBlog'] = true;
    $data['headers'] = [0 => ['Blog' => BASE_URL.'/#blog'], 
                        1 => ['Categorie' => BASE_URL.'/category'] ];
                        
		$categorys = $category->getAll();

    foreach ($categorys as $key => $cat) 
    {
      $blogCategory = $blog->filterAllByCategory($cat->category);
      $data['categorys'][$cat->category] = count($blogCategory);
    }

    $data['countBlog'] = count($blog->all());
		Controllers::loadView('category.php',$data);
	}

  public function search($text = null, $page = null)
  {
    if (empty($_POST)) {
      header('Location: '. BASE_URL .'/blog');
      die;
    }
    
		$home = new GeneralesModel("home");
    $contact = new FrontModel("contact");
    $data['contact'] = $contact->getAll();
		$data['home'] = $home;
    $data['pageBlog'] = true;
    $data['isSearch'] = true;

    $blog = new BlogModel();
		$category = new CategoryBlogModel();
		$categorys = $category->getAll();

    foreach ($categorys as $key => $cat) 
    {
      $blogCategory = $blog->filterAllByCategory($cat->category);
      $data['categorys'][$cat->category] = count($blogCategory);
    }

    if (empty($_POST['query'])) {
      $data['error'] = 'Ce champs est requis!';
    }
    
    $offset = 0;
    $i = 1;

    if (isset($text)) {
      $i = $page;
      $offset = ($i - 1) * 6;
    }

    if (isset($_POST['page']) && $_POST['page'] === 'category') {
      $data['isCategory'] = true;

      if (isset($_POST['category']) && $_POST['category'] != '') {
        $slug = $_POST['category'];
        $data['categorySelected'] = $slug;
        
        $title = urldecode($slug);
        $title = str_replace("-", " ", $title);
        
        $data["blogs"] = $blog->filterByQueryAndCategoryWithOffset($title, $_POST['query'], $offset);
        $data['blog_number'] = ceil(count($blog->filterAllByQueryAndCategory($title, $_POST['query'])) / 6) ;
        $data['blog_page'] = $i ;

        if (empty($blogs)) {
          $data['headers'] = [0 => ['Blog' => BASE_URL.'/#blog'], 
                              1 => ['Categorie' => BASE_URL.'/category'],
                              2 => [ucfirst($title) => BASE_URL.'/category/'.$slug] ];
        }
        else{
          $data['headers'] = [0 => ['Blog' => BASE_URL.'/#blog'], 
                              1 => ['Categorie' => BASE_URL.'/category'],
                              2 => [$data["blogs"][0]->category => BASE_URL.'/category/'.$slug] ];
        }

      }
      else{
        $data['headers'] = [0 => ['Blog' => BASE_URL.'/#blog'], 
                            1 => ['Categorie' => BASE_URL.'/category'] ];

        $data["blogs"] = $blog->filterByQueryWithOffset($_POST['query'], $offset);
        $data['blog_number'] = ceil(count($blog->filterAllByQuery($_POST['query'])) / 6) ;
        $data['blog_page'] = $i ;
      }
    }
    else{
      $data['headers'] = [0 => ['Blog' => BASE_URL.'/#blog']];

      $data["blogs"] = $blog->filterByQueryWithOffset($_POST['query'], $offset);
      $data['blog_number'] = ceil(count($blog->filterAllByQuery($_POST['query'])) / 6) ;
      $data['blog_page'] = $i ;
    }

    $data['query'] = $_POST['query']; 
		$data['countBlog'] = count($blog->all());
    Controllers::loadView('category.php',$data);
  }
}