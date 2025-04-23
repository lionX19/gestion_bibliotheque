<?php
return [
    'openai_api_key' => 'YOUR_API_KEY_HERE', // À remplacer par votre clé API OpenAI
    'responses' => [
        'greeting' => [
            'Bonjour ! Je suis l\'assistant de la bibliothèque. Comment puis-je vous aider ?',
            'Bienvenue ! Je suis là pour vous aider avec la gestion de la bibliothèque.',
            'Bonjour ! Je peux vous aider à trouver des livres, vérifier les emprunts et plus encore.'
        ],
        'help' => [
            'Je peux vous aider avec les questions suivantes :',
            'Voici ce que je peux faire pour vous :',
            'Je peux vous renseigner sur :'
        ],
        'topics' => [
            'emprunt' => [
                'questions' => ['emprunt', 'prêt', 'emprunter', 'livre'],
                'reponses' => [
                    'Pour emprunter un livre, vous devez être adhérent de la bibliothèque.',
                    'La durée maximale d\'un emprunt est de 3 semaines.',
                    'Vous pouvez emprunter jusqu\'à 5 livres simultanément.'
                ]
            ],
            'adhesion' => [
                'questions' => ['adhésion', 'inscription', 'adhérer', 'membre'],
                'reponses' => [
                    'Pour vous inscrire, vous devez présenter une pièce d\'identité et un justificatif de domicile.',
                    'L\'adhésion annuelle coûte 20€.',
                    'Les étudiants bénéficient d\'une réduction de 50% sur l\'adhésion.'
                ]
            ],
            'horaires' => [
                'questions' => ['horaires', 'ouvert', 'fermé', 'heure'],
                'reponses' => [
                    'La bibliothèque est ouverte du mardi au samedi de 9h à 18h.',
                    'Le dimanche et le lundi, la bibliothèque est fermée.',
                    'Pendant les vacances scolaires, les horaires sont modifiés.'
                ]
            ]
        ],
        'default' => [
            'Je ne suis pas sûr de comprendre votre demande. Voici ce que je peux faire :\n' .
                '- Rechercher des livres (ex: "cherche un livre de Victor Hugo")\n' .
                '- Vérifier la disponibilité (ex: "est-ce que le livre X est disponible ?")\n' .
                '- Informations sur les emprunts (ex: "qui a emprunté le livre X ?")\n' .
                '- Informations sur les adhérents (ex: "combien de livres a emprunté l\'adhérent X ?")\n' .
                '- Statistiques (ex: "montre-moi les statistiques des emprunts")',
            'Désolé, je n\'ai pas compris. Pouvez-vous reformuler votre question ? Vous pouvez dire "aide" pour voir toutes mes fonctionnalités.',
            'Je ne comprends pas votre demande. Tapez "aide" pour voir la liste des commandes disponibles.'
        ]
    ]
];
