<?php

namespace Skylab170\InstagramPhp\models;


use Skylab170\InstagramPhp\lib\Model;
use Skylab170\InstagramPhp\lib\Database;
use PDO;
use PDOException;


class User extends Model{

    private int $id;
    private array $posts;
    private string $profile;
    private array $followed;
    private array $followers;

    public function __construct(private string $username, private string $password){
        parent::__construct();
        $this->posts=[];
        $this->profile="";
        $this->followers=[];
        $this->followed=[];
    }

    public function setProfile($profile){
        $this->profile=$profile;
    }

    public function getprofile(){
        return $this->profile;
    }

    public function countGetPosts(){
        return count($this->posts);
    }

    public function countGetFollowers(){
        return count($this->followers);
    }

    public function countGetFollowed(){
        return count($this->followed);
    }

    public function getFollowers(){
        return $this->followers;
    }

    public function getFollowed(){
        return $this->followed;
    }

    public function publish(PostImage $post){
        try {
            //guardamos el post
            $query=$this->prepare('INSERT INTO posts (user_id, title, media) VALUES (:user_id, :title, :media)');
            $query->execute([
                'user_id'=>$this->id,
                'title'=>$post->getTitle(),
                'media'=>$post->getImage()
            ]);
        } catch (PDOException $th) {
            echo $th->getMessage();
        }
    }

    public function save(){
        
        try {
          
                $hash=$this->getHashedPassword($this->password);
                $query=$this->prepare('INSERT INTO users (username, password, profile) VALUES (:username, :password, :profile)');
                $query->execute([
                    'username'=>$this->username,
                    'password'=>$hash,
                    'profile'=>$this->profile
                ]);
                return true;
            
            
        } catch (PDOException $e) {
            //error_log($e->getMessage());
            echo "Error al guadrar" . $e->getMessage();
            return false;
        }
    }

    private function getHashedPassword($password){
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);
    }

    //VERIFICAR SI EXISTE EL USUARIO
    public static function exits($username){
        try {
            $db=new Database();
            $query=$db->connect()->prepare('SELECT username FROM users WHERE username= :username');
            $query->execute(['username'=>$username]);
            //SI LA CONSULTA OBTIENE UN RESULTADO
            if ($query->rowCount()>0) {
                return true;
            }else{
                return false;
            }
        } catch (PDOException $e) {
            echo $e;
            return false;
        }
    }

  

    public function comparePasswords($current){
        try{
            return password_verify($current, $this->password);
        }catch(PDOException $e){
            return NULL;
        }
    }

    //traer usuario por Id
    public static function getById ($id){
        try{
            $db=new Database();
            $query=$db->connect()->prepare('SELECT * FROM users WHERE user_id=:user_id');
            //se ejecuta la query y le pasamos el parametro
            $query->execute(['user_id'=>$id]);
            //guardar la informacion en una variable
            $data=$query->fetch(PDO::FETCH_ASSOC);
            $user=new User($data['username'], $data['password']);
            $user->setId($data['user_id']);
            $user->setProfile($data['profile']);
            return $user;

        }catch(PDOException $e){
            error_log($e->getMessage());
            return NULL;
        }
    }

    
     //traer usuario por username
     public static function getByUsername ($username){
        try{
            $db=new Database();
            $query=$db->connect()->prepare('SELECT * FROM users WHERE username=:username');
            //se ejecuta la query y le pasamos el parametro
            $query->execute(['username'=>$username]);
            //guardar la informacion en una variable
            $data=$query->fetch(PDO::FETCH_ASSOC);
            $user=new User($data['username'], $data['password']);
            $user->setId($data['user_id']);
            $user->setProfile($data['profile']);
            return $user;
        }catch(PDOException $e){
            error_log("ERORORORORO=>" . $e->getMessage());
        }
    }

    public function fetchPosts(){
        $this->posts=POstImage::getAll($this->id);
    }

    //LE ASIGNO A MI ARRAY POST EL ARRAY RETORNADO CON TODOS LOS POST DE LOS USUARIOS A LOS QUE SIGUE ESE USER
    public function fetchPostsFollowers(){
        $this->posts=POstImage::getPostFollowers($this->id);
    }

    //traer y asignar al array los seguidores
    public function fetchFollowed(){
        $this->followed=Follower::getFollowed($this->id);
    }

    //traer y asignar al array los seguidos
    public function fetchFollowers(){
        $this->followers=Follower::getFollowers($this->id);
    }

    //RECIBIMOS EL USUARIO QUE VAN A SEGUIR
    public function addSeguidor(User $user){
        //EXTRAMOS PRIMERAMENTE EL USUARIO QUE REALIZA LA ACCION, Y DESPUES EXTRAEMOS EL ID DEL QUE VAN A SEGUIR
        $follower=new Follower($this->id, $user->getId());
        $follower->existFollow();
    }

    public function getId():string{
        return $this->id;
    }

    public function setId(string $value){
        $this->id = $value;
    }

    public function getUsername(){
        return $this->username;
    }

    public function getPosts(){
        return $this->posts;
    }

    public function setPosts($value){
        $this->posts = $value;
    }

   

}

?>