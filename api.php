<?php

	require_once('rest.php');

	class API extends REST {
		public function __construct()
		{
			parent::__construct();
			$this->processApi();
		}

		

		// processApi
		public function processApi()
		{	
			$func = strtolower(trim(str_replace("/", "", $_REQUEST['action'])));
			if((int) method_exists($this, $func) > 0)
			{
				$this->$func();
			}
			else
			{
				$this->response('',400);
			}
		}

		// login
		private function login()
		{
			if($this->get_request_method() != "POST")
			{
				$this->response($this->json(array('status'=>'false','message'=>'Request Method Not Allowed'), 405));
			}
			if(isset($this->_request['email']) 
					&& isset($this->_request['password'])
					&& !empty($this->_request['email'])
					&& !empty($this->_request['password']))
				{

					$email = $this->_request['email'];
					$password = $this->_request['password'];
					if(filter_var($email , FILTER_VALIDATE_EMAIL ))
					{
						$sql = "SELECT * FROM `users` WHERE `email`='$email' AND `password`='$password'";
						$q = mysqli_query($this->dbconnect() , $sql);
						if(mysqli_num_rows($q) > 0)
						{
							$res = array('status'=>'true', 'message'=>'User Successfully Logged in');
							$this->response($this->json($res),200);
						}else
						{
							$res = array('status'=>'false','message'=>"Wrong Email/Password");
							$this->response($this->json($res),404);
						}
					} 
				}
				$error = array('status'=>'false', 'message'=>'Invalid Email/Password');
				$this->response($this->json($error), 200);
		}

		private function register()
		{
			if($this->get_request_method() != "POST")
			{
				$this->response($this->json(array('status'=>'false','message'=>'Request Method Not Allowed'), 405));
			}

			if(isset($this->_request['name'])
				&& isset($this->_request['email'])
				&& isset($this->_request['password']))
			{
				$name = $this->_request['name'];
				$email = $this->_request['email'];
				$password = $this->_request['password'];
				$query = mysqli_query($this->dbconnect(),"SELECT `email` FROM `users` WHERE `email`='$email'");
				$rec = mysqli_fetch_array($query);
				if($rec > 0)
				{
					$this->response($this->json(array('status'=>'false','message'=>'Already Exists'), 302));
				}else{	
				if(filter_var($email , FILTER_VALIDATE_EMAIL ))
					{
						$sql = "INSERT INTO `users` (`name`,`email`,`password`) VALUES ('$name','$email','$password')";
						$q = mysqli_query($this->dbconnect(),$sql);
						if($q > 0)
						{
							$res = array('status'=>'true', 'message'=>'User Successfully Registered');
							$this->response($this->json($res),200);
						}else
						{
							$res = array('status'=>'false','message'=>"Parameter Missing");
							$this->response($this->json($res),404);
						}
					}
				}
			}else
			{
				$error = array('status'=>'false', 'message'=>'Parameters Missing');
				$this->response($this->json($error), 200);			
			}

		}

		// imageUpload
		private function imageUpload()
		{
			if($this->get_request_method() != "POST")
			{
				$this->response($this->json(array('status'=>'false','message'=>'Request Method Not Allowed'), 405));
				// $this->response($this->json("status" => "false" , "message" => "Request Method is Not Allowed"),405);
			}

			if(isset($_FILES['img_name']['name']) && isset($this->_request['uploader_id']))
			{
				$name = time().$_FILES['img_name']['name'];
				$type = $_FILES['img_name']['type'];
				$tmp = $_FILES['img_name']['tmp_name'];
				$path = "upload/".$name;
				$uploader_id = $this->_request['uploader_id'];

				if($type == "image/png" || $type == "image/jpg" || $type == "image/jpeg")
				{
					if(move_uploaded_file($tmp,$path))
					{
						$query = mysqli_query($this->dbconnect(),"INSERT INTO `images` (`img_name`,`img_path`,`uploader_id`) VALUES ('$name','$path','$uploader_id')");
						if($query > 0)
						{
							$res = array("status" => "true", "message"=>'Successfully Inserted');
							$this->response($this->json($res),200);
						}
						else
						{
							$res = array("status" => "false", "message"=>'Insertion Failed');
							$this->response($this->json($res),406);
						}
					}
					else
					{
						$res = array("status" => "false", "message"=>'Image Uploading Failed');
						$this->response($this->json($res),200);
					}

				}
				else
				{
					$res = array("status" => "false", "message"=>'Invalid Image Type');
					$this->response($this->json($res),406);
				}

			}
			else
			{
				$res = array("status" => "false", "message"=>'Parameter Missing');
				$this->response($this->json($res),200);
			}
		}

		// imageComments
		private function imageComments()
		{
			if($this->get_request_method() != "POST")
			{
				$this->response($this->json(array("status" => "false" , "message" => "Request Method Not Allowed")),405);
			}	
				if(isset($this->_request['image_id']) 
					&& isset($this->_request['commentor_id'])
					&& isset($this->_request['comment_txt'])
					&& !empty($this->_request['image_id'])
					&& !empty($this->_request['commentor_id'])
					&& !empty($this->_request['comment_txt']))
					{
						$image_id =  $this->_request['image_id'];
						$commentor_id = $this->_request['commentor_id'];
						$comments = $this->_request['comment_txt']; 
						$query = mysqli_query($this->dbconnect(),"INSERT INTO `Image_comments` (`image_id`,`commentor_id`,`comment_txt`) VALUES ('$image_id','$commentor_id','$comments')");
						if($query > 0)
						{
							$res = array("status" => "false", "message"=>'Successfully Added Comment');
							$this->response($this->json($res),200);
						}
						else
						{
							$res = array("status" => "false", "message"=>'failed');
							$this->response($this->json($res),406);
						}
					}else{
						$res = array("status" => "false", "message"=>'Parameter Missing');
						$this->response($this->json($res),200);
					}
			}

		private function getImageCommment()
		{
			if($this->get_request_method() != "POST")
			{
				$res = array("status"=>"false" , "message" => "Request Method Not Allowed");
				$this->response($this->json($res),405);
			}

			if(isset($this->_request['image_id'])
				&& isset($this->_request['commentor_id'])
				&& !empty($this->_request['image_id'])
				&& !empty($this->_request['commentor_id']))
			{
				$image_id = $this->_request['image_id'];
				$commentor_id = $this->_request['commentor_id'];
				$query = mysqli_query($this->dbconnect(),"SELECT * FROM `image_comments` JOIN  WHERE `image_id`='$image_id' AND `commentor_id`='$commentor_id'");
				if($rec = mysqli_fetch_array($query))
				{
					$res = array('image_id'=> $rec['image_id'],
								 'commentor_id' => $rec['commentor_id'],
								 'comment_txt' => $rec['comment_txt'],
								 'created_at' => $rec['created_date']
								 );
					$this->response($this->json($res),200);
				}
				else
				{
					$res = array("status" => "false", "message"=>'Record Not Found');
					$this->response($this->json($res),404);
				}
			}
			else{
				$res = array("status" => "false", "message"=>'Parameter Missing');
				$this->response($this->json($res),200);
			}
		}	
		

		// json
		private function json($data)
		{
			if(is_array($data))
			{
				return json_encode($data);
			}
		}
	}

	$API = new API;
 ?>