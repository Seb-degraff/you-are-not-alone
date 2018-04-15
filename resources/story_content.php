<?php

//"{$joueurs} va mourrir en";
//"{$joueurs} est en sécuritlé en";

return [
    'scenarios' => [
        [
            'title' => "Le pont levis",
            'story' => "Afin d’accéder à l’entrée du donjon, vous devez traverser un pont-levis. Mais en observant plus attentivement, vous remarquez que le bois est vieux, tout craquelé, et risquerait de se briser à tout moment. La chute serait mortelle.",
            'choice1' => "Se séparer pour trouver une autre entrée",
            'choice0' => "Tenter de passer le pont.",
            'visionChoice1' => "en cherchant une autre entrée.",
            'visionChoice0' => "en tentant de passer le pont.",
            'winChoice1' => "Vous avez fait le bon choix. En tâtant le sol, vous trouvez une trappe qui mêne directement à l’entrée du donjon, et vous accédez à la prochaine épreuve.",
            'winChoice0' => "Le pont craque de partout, mais votre délicatesse vous a sauvé ! Vous continuez l’épreuve.",
            'looseChoice1' => "Alors que vous cherchez une autre entrée, un bras de mort-vivant sort de la terre et vous saisit la jambe, avant de vous emporter avec lui dans les abysses !",
            'looseChoice0' => "CRACK ! une des planches du pont-levis se fracture sous vos pieds, et vous tombez dans le vide, avant de vous briser la nuque au sol.",
            'image' => "http://gold.arrache.ch/public/images/pont_levis.png",
        ],
        [
            'title' => "Les deux chemins",
            'story' => "Vous êtes face à deux passages différents. Celui de gauche ne présente pas de danger particulier, mais celui de droite dégage une odeur de brûlé et des grognements inquiétants. Un dragon surveille probablement ce couloir.",
            'choice1' => "Vous passez à gauche, évidemment.",
            'choice0' => "Vous passez à droite, avec crainte.",
            'visionChoice1' => "en passant à gauche.",
            'visionChoice0' => "en passant à droite.",
            'winChoice1' => "Votre intuition était correcte, le passage ne vous a pas présenté d’opposition.",
            'winChoice0' => "Vous avez été assez silencieux pour ne pas réveiller le dragon. Vous trouvez la prochaine épreuve.",
            'looseChoice1' => "Lorsque que vous avancez dans ce passage qui avait l’air sans danger, vous remarquez que les murs se resserrent sur vous peu à peu. Malgré votre course désespérée, vous mourrez écrasé avec comme dernière vision, la porte de sortie !",
            'looseChoice0' => "Vous faites involontairement du bruit... un cri assourdissant retentit dans le couloir, suivi d’un souffle enflammé. Vous n’êtes plus qu’un vulgaire tas de cendres.",
            'image' => "http://gold.arrache.ch/public/images/chemins.png",
        ],
        [
            'title' => "Les Gobelins",
            'story' => "Une horde de gobelins affamés se précipite sur vous. Leur force est bien supérieure à la vôtre, mais ils n’ont aucune stratégie. La situation peut tourner à votre avantage si vous restez défensifs.",
            'choice1' => "Les attendre et se défendre.",
            'choice0' => "Foncer dans le tas.",
            'visionChoice1' => "en se défendant.",
            'visionChoice0' => "en fonçant dans le tas.",
            'winChoice1' => "Les gobelins sont désorganisés dans leurs attaques, vous anticipez leur coups et vous réussissez à les occire un à un.",
            'winChoice0' => "Votre instinct de survie déclenche une rage incontrôlable. Votre arme s’écrase avec brutalité sur le crâne de vos adversaires, en décimant plus d’un. Les autres créatures sont alors effrayées et refusent de s’attaquer à vous.",
            'looseChoice1' => "Votre défense est inébranlable, mais malheureusement, vous êtes touché par la déviation d’une flèche empoisonnée, dont vous ne connaissez pas l’origine. Vous mourrez sur le coup !",
            'looseChoice0' => "Bien que votre charge soit héroïque, votre sottise vous a fait courir à votre perte. Dans votre course, vous trébuchez et vous vous empalez sur la lance d’un gobelin.",
            'image' => "http://gold.arrache.ch/public/images/goblins.png",
        ],
        [
            'title' => "L'ombre et la lumière",
            'story' => "Devant vous se trouvent deux portails. De l’un s’échappe une lumière divine et chaleureuse. De l’autre, s’échappe une émanation maléfique et effrayante.",
            'choice1' => "Passer le portail divin.",
            'choice0' => "Passer le portail maléfique",
            'visionChoice1' => "en passant le portail divin.",
            'visionChoice0' => "en passant le protail maléfique.",
            'winChoice1' => "Une douce voix angélique s’adresse à vous : «félicitation voyageur, vos actes de bravoure ont été approuvés par les dieux, vous accédez à la dernière épreuve. » La lumière divine vous soigne de vos blessures contre les gobelins et vous redonne du courage, avant de vous téléporter dans la dernière salle.",
            'winChoice0' => "Qu’avant nous là ? Un spécimen intéressant ! Vous ne suivez aucune voie ni personne. Vous pourriez peut-être m’être utile un jour. Vous méritez que je vous garde en vie. » Des ombres vous entoure, vous procurant détermination et combativité, puis vous transfèrent vers la dernière épreuve.",
            'looseChoice1' => "Une voix sévère résonne avec force dans votre tête : « REPENTEZ VOUS ! Vous n’êtes pas digne ! Subissez le courroux céleste ! Nous allons vous purifier par le feu ! » Votre peau se désintègre et vous apercevez vos muscles, mais lorsque la vie vous quitte, vous ressentez une agréable sensation. Vous êtes mort, mais libéré de vos péchés.",
            'looseChoice0' => "un chuchotement méprisant atteint votre oreille : « Votre courage me répugne ! Vous n’êtes qu’un pantin des dieux. Je dois malheureusement vous supprimer. » Des ombres s’emparent de votre corps et vous immobilisent, avant de vous comprimer jusqu’à vous faire disparaitre dans le néant distordu.",
            'image' => "http://gold.arrache.ch/public/images/portails.png",
        ],
        [
            'title' => "Vous n'y toucherez pas",
            'story' => "Vous arrivez enfin dans la dernière salle. Tout au fond se tient une quantité incroyable de monnaies et de bijoux, baignés dans une aura sacrée. Alors que vous marchez dans leur direction, le maître du jeu se téléporte devant vous, dans un nuage de fumée. Il vous regarde d’un air amusé. « Intéressant, vous avez réussi à venir à bout de tout les dangers de ce donjon. Je ne vous croyais pas aussi futés. Mais vous croyez que je vais vous laisser le trésor aussi facilement ? PAUVRES FOUS ! Subissez mon courroux ! » Subitement, le gentil magicien qui vous a guidé tout au long de votre périple se transforme en effroyable nécromancien, prêt à vous annihiler. Vous remarquez que des calices de sang se sont élevés dans toute la pièce, afin d’alimenter sa puissante magie. Vous comprenez qu’il faut les détruire pour l’affaiblir. Étrangement, vous constatez aussi que sur un plateau se trouve un délicieux cupcake.",
            'choice1' => "Détruire les calices.",
            'choice0' => "Aller vers le cupcake",
            'visionChoice1' => "en détruisant les calices.",
            'visionChoice0' => "en allant vers le cupcacke.",
            'winChoice1' => "Vous atteignez avec succès l’un des réservoirs de sang. D’un coup bien porté, vous le brisez. Vous remarquez alors que les pouvoirs du sorcier n’ont plus d’effet sur vous. Vous réussissez à fuir avec le butin.",
            'winChoice0' => "Alors que le mage s’occupe des autres aventuriers, vous avez perdu tout espoir dans cette quête. D’un pas nonchalant, vous arrivez devant la pâtisserie, votre dernière volonté. Juste avant de le savourer, le sorcier vous interpelle d’une voix suppliante : « NOOOOON !!! Je vous interdit de toucher à ça ! C’est mon dernier Cupcake à la double crème, orné d’éclats chocolatés ! En plus, c’est mon dernier ! Prenez ce que vous voulez et PARTEZ ! Mais ne le touchez pas s’il vous plaît ! » Vos yeux s’illuminent : vous menacez le maître du jeu avec le cupcake, tout en vous approchant du butin. Vous le saisissez et vous fuyez en laissant le petit gâteau derrière vous.",
            'looseChoice1' => "Alors que vous dégainez votre arme pour démolir une des coupes de sang, le nécromancien vous fixe de son regard ténébreux. Vous n’arrivez plus à le quitter des yeux. Votre âme est aspirée lentement en direction du bâton maléfique que le mage tient dans sa main.",
            'looseChoice0' => "Intrigué par la présence de la confiserie, vous décidez de courir vers cette dernière. Le sorcier vous repère aussitôt. « Espèce de goinfre ! Tout le monde se bat ici, et vous ne pensez qu’à vous empiffrer ! » D’un geste précis de la main, il vous transforme aussitôt en mignon petit porcelet.",
            'image' => "http://gold.arrache.ch/public/images/butin.png",
        ],
    ]
];