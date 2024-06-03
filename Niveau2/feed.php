<?php session_start();
?>

<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Flux</title>         
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
             * Cette page est TRES similaire à wall.php. 
             * Vous avez sensiblement à y faire la meme chose.
             * Il y a un seul point qui change c'est la requete sql.
             */
            /**
             * Etape 1: Le mur concerne un utilisateur en particulier
             */
            $userId = intval($_GET['user_id']);
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
                $laQuestionEnSql = "SELECT * FROM `users` WHERE id= '$userId' ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                $user = $lesInformations->fetch_assoc();
                //@todo: afficher le résultat de la ligne ci dessous, remplacer XXX par l'alias et effacer la ligne ci-dessous
                echo "<pre>" . print_r($user, 1) . "</pre>";
                ?>
                <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez tous les messages des utilisatrices
                        auxquel est abonnée l'utilisatrice <?php echo $user['alias'] ?>(n° <?php echo $user['id'] ?>)
                    </p>

                </section>
            </aside>
            <main>
                <?php
                /**
                 * Etape 3: récupérer tous les messages des abonnements
                 */
                $laQuestionEnSql = "
                    SELECT posts.content,
                    posts.created,
                    users.alias as author_name,  
                    count(likes.id) as like_number,  
                    GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM followers 
                    JOIN users ON users.id=followers.followed_user_id
                    JOIN posts ON posts.user_id=users.id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE followers.following_user_id='$userId' 
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
                 * A vous de retrouver comment faire la boucle while de parcours...
                 */

                 while ($feed = $lesInformations->fetch_assoc()) {
                    // Séparer la chaîne de caractères en un tableau
                    $tags = explode(',', $feed['taglist']);

                 
                ?>                
                
                <article>
                <?php 
                $authorName = $feed['author_name'];

                $userInfoSQL = "SELECT id FROM `users` WHERE alias = '$authorName'"; 
                $userLabel = $mysqli->query($userInfoSQL);
                $userName = $userLabel->fetch_assoc();


                // $originalDate = $post['created'];
                // // Convertir la chaîne de date en timestamp Unix
                // $unixTime = strtotime($originalDate);
                // // Convertir le timestamp Unix en chaîne de date dans le format souhaité
                // $newDate = date("d F Y à H\hi", $unixTime);

                ?>
                    <h3>
                        <time datetime='2020-02-01 11:12:13' >31 février 2010 à 11h12</time>
                    </h3>
                    <address>par <a href="wall.php?user_id=<?php echo $userName['id'] ?>"><?php echo $authorName ?></address>
                    <div>
                        <p><?php echo $feed['content'] ?></p>
                    </div>                                            
                    <footer>
                        <small>♥<?php echo $feed['like_number'] ?></small>
                        <?php foreach ($tags as $tag) { 
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
                <?php }
                // et de pas oublier de fermer ici vote while
               ?>
            


            </main>
        </div>
    </body>
</html>
