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
    ],

    /**
     * --------------------------------------------------------------------------
     *     Tipos de Nodes permitidos
     * --------------------------------------------------------------------------
     *  Essa opção inclui os tipos de nodes permitidos no sistema
     */
    'allowedTypeNodes' => [
        'start',
        'end',
        'input',
        'output'
    ],

    /**
     * --------------------------------------------------------------------------
     *     Tipos de dados
     * --------------------------------------------------------------------------
     *  Essa opção inclui os tipos de dados permitidos no sistema
     */
    'allowedDataTypes' => [
        'str',
        'int',
        'float',
        'bool'
    ],

    /**
     * --------------------------------------------------------------------------
     *     Tipos de output
     * --------------------------------------------------------------------------
     *  Essa opção inclui os tipos de dados permitidos no Output
     */
    'allowedOutputTypes' => [
        'var',
        'text'
    ],
];
