<?php include 'activeSession.php' ?>

<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Actualités</title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
    </head>
    <body>
        <header>
            <a href='admin.php'><img src="resoc.jpg" alt="Logo de notre réseau social"/></a>
            <nav id="menu">

<?php include 'tab.php' ?>

            </nav>
            <nav id="user">
                <a href="#">▾ Profil</a>
                <ul>
                    <li><a href="settings.php?user_id=<?php echo $_SESSION['connected_id'] ?>">Paramètres</a></li>
                    <li><a href="followers.php?user_id=<?php echo $_SESSION['connected_id'] ?>">Mes suiveurs</a></li>
                    <li><a href="subscriptions.php?user_id=<?php echo $_SESSION['connected_id'] ?>">Mes abonnements</a></li>
                </ul>
            </nav>
        </header>
        <div id="wrapper">
            <aside>
                <img src="img_news.jpg" alt="Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez les derniers messages de
                        tous les utilisatrices du site.</p>

                </section>
            </aside>
            <main>
    
                <?php
                // Etape 1: Ouvrir une connexion avec la base de donnée.
                include 'authentication.php';        
                //verification
                if ($mysqli->connect_errno)
                {
                    echo "<article>";
                    echo("Échec de la connexion : " . $mysqli->connect_error);
                    echo("<p>Indice: Vérifiez les parametres de <code>new mysqli(...</code></p>");
                    echo "</article>";
                    exit();
                }

                // Etape 2: Poser une question à la base de donnée et récupérer ses informations
                // cette requete vous est donnée, elle est complexe mais correcte, 
                // si vous ne la comprenez pas c'est normal, passez, on y reviendra
                $laQuestionEnSql = "
                    SELECT posts.content,
                    posts.created,
                    users.alias as author_name,  
                    count(likes.id) as like_number,  
                    GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM posts
                    JOIN users ON  users.id=posts.user_id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC  
                    LIMIT 10
                    ";

                $lesInformations = $mysqli->query($laQuestionEnSql);
                // Vérification
                if ( ! $lesInformations)
                {
                    echo "<article>";
                    echo("Échec de la requete : " . $mysqli->error);
                    echo("<p>Indice: Vérifiez la requete  SQL suivante dans phpmyadmin<code>$laQuestionEnSql</code></p>");
                    exit();
                }


                // Etape 3: Parcourir ces données et les ranger bien comme il faut dans du html
                // NB: à chaque tour du while, la variable post ci dessous reçois les informations du post suivant.
                while ($post = $lesInformations->fetch_assoc())
                {   
                    // Séparer la chaîne de caractères en un tableau
                    $tags = explode(',', $post['taglist']);

                    $originalDate = $post['created'];
                    // Convertir la chaîne de date en timestamp Unix
                    $unixTime = strtotime($originalDate);
                    // Convertir le timestamp Unix en chaîne de date dans le format souhaité
                    $newDate = date("d F Y à H\hi", $unixTime);
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
                            <small>❤️<?php echo $post['like_number'] ?></small>
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
                    <?php
                }
                ?>

            </main>
        </div>
    </body>
</html>
