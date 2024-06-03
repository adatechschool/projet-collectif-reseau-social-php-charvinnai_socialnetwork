<?php
  $res = $mysqli->query($lInstructionSql);
                        $user = $res->fetch_assoc();
    if ( ! $user OR $user["password"] != $passwdAVerifier)
      {
         echo "La connexion a échouée. ";
                            
         } else
         {
            echo "Votre connexion est un succès : " . $user['alias'] . ".";
             // Se souvenir que l'utilisateur s'est connecté pour la suite
            // documentation: https://www.php.net/manual/fr/session.examples.basic.php
                  $_SESSION['connected_id']=$user['id'];
         }
                    
  ?>                     