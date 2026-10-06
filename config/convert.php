<?php

return [
    /**
     * --------------------------------------------------------------------------
     *     Linguagens de entrada permitidas
     * --------------------------------------------------------------------------
     *  Essa opção serve para definir quais linguagens são permitidas para 
     * serem convertidas, retornando um array das linguagens.
     *  Nessa opção, será pego as linguagens e conferidas para permitir o 
     * acesso via url /api/convert/{sourceLanguages}/...
     * 
     */
    'sourceLanguages' => [
        'flowchart'
    ],

    /**
     * --------------------------------------------------------------------------
     *     Linguagens de saída permitidas
     * --------------------------------------------------------------------------
     *  Essa opção serve para definir quais linguagens de saída são permitidas 
     * para serem convertidas, retornando um array das linguagens.
     *  Nessa opção, será pego as linguagens e conferidas para permitir o 
     * acesso via url /api/convert/{sourceLanguages}/to/{targetLanguages}
     */

    'targetLanguages' => [
        'python'
    ]
];
