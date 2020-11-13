# Yet another text generator based on Markov chain on PHP

Example:

```php
$sentence = 'any entry showing that sector 123 is occupied';
$model = new Model(1);
$model->learnFromSentence($sentence);
echo $model->generateSentence(10);
```