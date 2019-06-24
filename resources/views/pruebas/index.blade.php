
<h1>{{$titulo}}</h1>

<ul>
	@foreach($animales as $animal)
		<li>{{$animal}}</li>
	@endforeach

	<?php foreach ($animales as $animal): ?>
		<?php echo "<li>".$animal."</li>" ?>
	<?php endforeach ?>
</ul>