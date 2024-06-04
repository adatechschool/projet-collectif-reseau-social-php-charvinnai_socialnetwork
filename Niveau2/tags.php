<?php session_start();
?>

<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Les message par mot-clé</title> 
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
             * Cette page est similaire à wall.php ou feed.php 
             * mais elle porte sur les mots-clés (tags)
             */
            /**
             * Etape 1: Le mur concerne un mot-clé en particulier
             */
            $tagId = intval($_GET['tag_id']);
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
                 * Etape 3: récupérer le nom du mot-clé
                 */
                $laQuestionEnSql = "SELECT * FROM tags WHERE id= '$tagId' ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                $tag = $lesInformations->fetch_assoc();
                //@todo: afficher le résultat de la ligne ci dessous, remplacer XXX par le label et effacer la ligne ci-dessous
                // echo "<pre>" . print_r($tag, 1) . "</pre>";
                ?>
                <img src="img_tags.jpg" alt="Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez les derniers messages comportant
                        le mot-clé "<?php echo $tag['label'] ?>"
                        (n° <?php echo $tagId ?>)
                    </p>

                </section>
            </aside>
            <main>
                <?php
                /**
                 * Etape 3: récupérer tous les messages avec un mot clé donné
                 */
                $laQuestionEnSql = "
                    SELECT posts.content,
                    posts.created,
                    users.alias as author_name,  
                    count(likes.id) as like_number,  
                    GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM posts_tags as filter 
                    JOIN posts ON posts.id=filter.post_id
                    JOIN users ON users.id=posts.user_id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE filter.tag_id = '$tagId' 
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
                while ($post = $lesInformations->fetch_assoc())
                {
                    $tags = explode(',', $post['taglist']);

                    $originalDate = $post['created'];
                    // Convertir la chaîne de date en timestamp Unix
                    $unixTime = strtotime($originalDate);
                    // Convertir le timestamp Unix en chaîne de date dans le format souhaité
                    $newDate = date("d F Y à H\hi", $unixTime);

                    // echo "<pre>" . print_r($post, 1) . "</pre>";
                        

                    ?>                
                    <article>
                        <h3>
                            <time><?php echo $newDate ?></time>
                        </h3>
                        <?php
                        $surname = $post['author_name'];
                                    // chemin en string
                                    $userInfoSQL = "SELECT id FROM `users` WHERE alias = '$surname'"; 
                                    // execution de la requete
                                    $userLabel = $mysqli->query($userInfoSQL);
                                    // affichage de la requete en array
                                    $userName = $userLabel->fetch_assoc();
                                    //var_dump($userName['id']);
                        ?>
                         <address>par <a href="wall.php?user_id=<?php echo $userName['id'] ?>"><?php echo $post['author_name'] ?></a></address>
                        <div>
                            <p><?php echo $post['content'] ?></p>
                        </div>                                            
                        <footer>
                            <small>♥ <?php echo $post['like_number'] ?></small>
                            <?php foreach ($tags as $tag) { ?>
                                <?php
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