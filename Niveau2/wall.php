<?php session_start();
?>



<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Mur</title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
    </head>
    <body>
        <header>
            <img src="resoc.jpg" alt="Logo de notre réseau social"/>
            <nav id="menu">
                <a href="news.php">Actualités</a>
                <a href="wall.php?user_id=<?php echo $_SESSION['connected_id'] ?>">Mur</a>
                <a href="feed.php?user_id=<?php echo $_SESSION['connected_id'] ?>">Flux</a>
                <a href="tags.php?tag_id=1">Mots-clés</a>
            </nav>
            <nav id="user">
                <a href="#">Profil</a>
                <ul>
                    <li><a href="settings.php?user_id=<?php echo $_SESSION['connected_id'] ?>">Paramètres</a></li>
                    <li><a href="followers.php?user_id=<?php echo $_SESSION['connected_id'] ?>">Mes suiveurs</a></li>
                    <li><a href="subscriptions.php?user_id=<?php echo $_SESSION['connected_id'] ?>">Mes abonnements</a></li>
                </ul>

            </nav>
        </header>
        <div id="wrapper">
            <?php
            /**
             * Etape 1: Le mur concerne un utilisateur en particulier
             * La première étape est donc de trouver quel est l'id de l'utilisateur
             * Celui ci est indiqué en parametre GET de la page sous la forme user_id=...
             * Documentation : https://www.php.net/manual/fr/reserved.variables.get.php
             * ... mais en résumé c'est une manière de passer des informations à la page en ajoutant des choses dans l'url
             */

            $userId =intval($_GET['user_id']);
                        ?>
            <?php
            /**
             * Etape 2: se connecter à la base de donnée
             */
            include 'authentication.php';
            ?>

            <aside>

              <?php
                /**
                 * Etape 3: récupérer le nom de l'utilisateur
                 */                
                $laQuestionEnSql = "SELECT * FROM users WHERE id= '$userId' ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                $userWall = $lesInformations->fetch_assoc();
                //@todo: afficher le résultat de la ligne ci dessous, remplacer XXX par l'alias et effacer la ligne ci-dessous
                //echo "<pre>" . print_r($userWall, 1) . "</pre>";
                ?>


                <img src="img_wall.jpg" alt="Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez tous les messages de l'utilisatrice : <?php echo $userWall['alias']?></p>
                    <form action="wall.php?user_id=<?php echo $userId ?>" method="post">
                    <input type='submit' name='followButton' value="S'abonner">
                    </form>

             <?php 
                if ($userWall['id'] != $_SESSION['connected_id']) 
                {

                if(isset($_POST['followButton']))
                    {
                        $followSql = "INSERT INTO followers (followed_user_id, following_user_id) "
                                        . "VALUES (" . $userWall['id'] . ", "
                                        .  $_SESSION['connected_id'] . ");";


                                        $executeFollowSQL = $mysqli->query($followSql);
                                        if ( ! $executeFollowSQL)
                                        {
                                            echo "Impossible d'ajouter le message: " . $mysqli->error;
                                            header("Location: wall.php?user_id=" . $userWall['id']);
                                            exit;   
                                        } else
                                        {
                                            echo "Vous êtes abonné.e.s à :" . $userWall['alias'];
                                            header("Location: wall.php?user_id=" . $userWall['id']);
                                            exit;    
                                        }
                
                    };

                }
            ?>


                </section>
            </aside>
            <main>
                <?php
                /**
                 * Etape 3: récupérer tous les messages de l'utilisatrice
                 */
                $laQuestionEnSql = "
                    SELECT posts.content, posts.created, users.alias as author_name, 
                    COUNT(likes.id) as like_number, GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM posts
                    JOIN users ON  users.id=posts.user_id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE posts.user_id='$userId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC  
                    ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                }

                /**
                 * Etape 4: @todo Parcourir les messsages et remplir correctement le HTML avec les bonnes valeurs php
                 */
?>
                 <article>
            <form action="wall.php?user_id=<?php echo $userWall['id'] ?>" method="post">
                 <dl>
                    <!--  <dt><label for='auteur'>Auteur</label></dt> -->
                    <?php
                        $connectedQuery = "SELECT alias FROM `users` WHERE id = " . $_SESSION['connected_id'];
                        $connectedExe = $mysqli->query($connectedQuery);
                        $connectedUser = $connectedExe->fetch_assoc();
                        ?>
                    <?php

                    if (isset($_POST['message'])) {
                     $authorId = $_SESSION['connected_id'];
                     $postContent = $_POST['message'];

                        $messageQuery = "INSERT INTO posts (id, user_id, content, created, parent_id) "
                        . "VALUES (NULL, "
                        .  "'" . $authorId . "', "
                        . "'" . $postContent . "', "
                        . "NOW(), "
                        . "NULL);"
                        ;

                        $ok = $mysqli->query($messageQuery);
                        if ( ! $ok)
                        {
                            echo "Impossible d'ajouter le message: " . $mysqli->error;
                        } else
                        {
                            echo "Message posté en tant que : " . $connectedUser['alias'];
                        }
                    }
                        ?>


                     <dd>What's up, <?php echo $connectedUser['alias']?>?</dd>
                     <!--  $_SESSION['connected_id']-->
                     <dt><label for='message'>Message</label></dt>
                     <dd><textarea name='message'></textarea></dd>
                 </dl>
                 <input type='submit'>
            </form>               
             </article>           
<?php
                while ($post = $lesInformations->fetch_assoc())
                {
                    // Séparer la chaîne de caractères en un tableau
                    $tags = explode(',', $post['taglist']);

                    //echo "<pre>" . print_r($post, 1) . "</pre>";
                    ?>     
                    <article>
                        <h3>
                            <time datetime='2020-02-01 11:12:13' ><?php echo $post['created'] ?></time>
                        </h3>
                        <address>par <?php echo $post['author_name'] ?></address>
                        <div>
                            <p><?php echo $post['content'] ?></p>
                        </div>                                            
                        <footer>
                            <small>♥ <?php echo $post['like_number'] ?></small>
                            <?php
                                    foreach ($tags as $tag) {

                                    // chemin en string
                                    $hashtagInfoSQL = "SELECT id FROM `tags` WHERE label = '$tag'"; 
                                    // execution de la requete
                                    $hashtagLabel = $mysqli->query($hashtagInfoSQL);
                                    // affichage de la requete en array
                                    $hashtag = $hashtagLabel->fetch_assoc();
                                    // var_dump($hashtag['id']);
                                ?>
                            <a href="tags.php?tag_id=<?php echo $hashtag['id'] ?>"><?php echo "#" . trim($tag) ?></a>
                            <?php } ?>
                        </footer>
                    </article>
                <?php } ?>


            </main>
        </div>
    </body>
</html>
