<?php include 'activeSession.php'; ?>

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
                <?php include 'tab.php'; ?>
            </nav>
            <nav id="user">
                <a href="#">Profil</a>
                <ul>
                    <li><a href="settings.php?user_id=<?php echo $_SESSION['connected_id']; ?>">Paramètres</a></li>
                    <li><a href="followers.php?user_id=<?php echo $_SESSION['connected_id']; ?>">Mes suiveurs</a></li>
                    <li><a href="subscriptions.php?user_id=<?php echo $_SESSION['connected_id']; ?>">Mes abonnements</a></li>
                </ul>
            </nav>
        </header>
        <div id="wrapper">
            <?php
            $userId = intval($_GET['user_id']);
            ?>

            <?php include 'authentication.php'; ?>

            <aside>
                <?php
                $laQuestionEnSql = "SELECT * FROM users WHERE id= '$userId' ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                $userWall = $lesInformations->fetch_assoc();
                ?>

                <img src="img_wall.jpg" alt="Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez tous les messages de l'utilisatrice : <?php echo $userWall['alias']; ?></p>

                    <?php 
                    if ($userWall['id'] != $_SESSION['connected_id']) {
                        $queryFollow = "SELECT 1 FROM followers WHERE followed_user_id = " . $userWall['id'] . " AND following_user_id = " .  $_SESSION['connected_id'] . ";";
                        $exeFollow = $mysqli->query($queryFollow);

                        if ($exeFollow->num_rows > 0) {
                            echo "<p>Vous êtes abonné.e.s</p>";
                        } else {
                            ?>
                            <form action="wall.php?user_id=<?php echo $userId; ?>" method="post">
                                <input type='submit' name='followButton' value="S'abonner">
                            </form>
                            <?php
                            if (isset($_POST['followButton'])) {
                                $followSql = "INSERT INTO followers (followed_user_id, following_user_id) VALUES (" . $userWall['id'] . ", " .  $_SESSION['connected_id'] . ");";
                                $executeFollowSQL = $mysqli->query($followSql);
                                if (!$executeFollowSQL) {
                                    echo "Impossible d'ajouter le message: " . $mysqli->error;
                                    header("Location: wall.php?user_id=" . $userWall['id']);
                                    exit;   
                                } else {
                                    echo "Vous êtes abonné.e.s à :" . $userWall['alias'];
                                    header("Location: wall.php?user_id=" . $userWall['id']);
                                    exit;    
                                }
                            }
                        }
                    }
                    ?>
                </section>
            </aside>
            <main>
                <?php
                $laQuestionEnSql = "
                    SELECT posts.content, posts.created, users.alias as author_name, 
                    COUNT(likes.id) as like_number, GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM posts
                    JOIN users ON  users.id=posts.user_id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags ON posts_tags.tag_id = tags.id 
                    LEFT JOIN likes ON likes.post_id = posts.id 
                    WHERE posts.user_id='$userId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                if (!$lesInformations) {
                    echo("Échec de la requete : " . $mysqli->error);
                }
                ?>

                <article>
                    <form action="wall.php?user_id=<?php echo $userWall['id']; ?>" method="post">
                        <dl>
                            <?php
                            $connectedQuery = "SELECT alias FROM `users` WHERE id = " . $_SESSION['connected_id'];
                            $connectedExe = $mysqli->query($connectedQuery);
                            $connectedUser = $connectedExe->fetch_assoc();

                            if (isset($_POST['message'])) {
                                $authorId = $_SESSION['connected_id'];
                                $postContent = $_POST['message'];

                                $messageQuery = "INSERT INTO posts (id, user_id, content, created, parent_id) "
                                    . "VALUES (NULL, '$authorId', '$postContent', NOW(), NULL);";

                                $ok = $mysqli->query($messageQuery);
                                if (!$ok) {
                                    echo "Impossible d'ajouter le message: " . $mysqli->error;
                                } else {
                                    echo "Message posté en tant que : " . $connectedUser['alias'];
                                }
                            }
                            ?>
                            <dd><strong>What's up, <?php echo $connectedUser['alias']; ?>?</strong></dd>
                            <dt><label for='message'>Message</label></dt>
                            <dd><textarea name='message'></textarea></dd>
                        </dl>
                        <input class="button" type='submit'>
                    </form>               
                </article> 

                <?php
                while ($post = $lesInformations->fetch_assoc()) {
                    $tags = explode(',', $post['taglist']);
                    ?>
                    <article>
                        <h3>
                            <time datetime='2020-02-01 11:12:13'><?php echo $post['created']; ?></time>
                        </h3>
                        <address>par <?php echo $post['author_name']; ?></address>
                        <div>
                            <p><?php echo $post['content']; ?></p>
                        </div>                                            
                        <footer>
                            <small>♥ <?php echo $post['like_number']; ?></small>
                            <?php
                            foreach ($tags as $tag) {
                                $hashtagInfoSQL = "SELECT id FROM `tags` WHERE label = '$tag'"; 
                                $hashtagLabel = $mysqli->query($hashtagInfoSQL);
                                $hashtag = $hashtagLabel->fetch_assoc();
                                ?>
                                <a href="tags.php?tag_id=<?php echo $hashtag['id']; ?>"><?php echo "#" . trim($tag); ?></a>
                            <?php } ?>
                        </footer>
                    </article>
                <?php } ?>
            </main>
        </div>
    </body>
</html>
