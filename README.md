# Yet another text generator based on Markov chain on PHP

## Install:

```bash
composer require svetlyi/markov-based-text-gen
```

## Example:

```php
$sentence = 'any entry showing that sector 123 is occupied';
$model = new Model(1);
$model->learnFromSentence($sentence);
echo $model->generateSentence(10);
```