<div class="form-group row" id="categories-accordion">
    <div class="container">
        <div class="card">
            <div class="card-header" id="heading2">
                <h5 class="mb-0">
                    <button type="button" class="btn btn-link" data-toggle="collapse" data-target="#collapse2" aria-expanded="false" aria-controls="collapse2" style="width: 100%; text-align: left;">
                        {{ $t->get('Categorieën (klik om open te klappen)') }}
                    </button>
                </h5>
            </div>
            <div id="collapse2" class="collapse" aria-labelledby="heading2" data-parent="#categories-accordion">
                <div class="card-body">
                    @foreach($categories as $category)
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="category-{{ $category->id }}" name="category-{{ $category->id }}" value="1" @if($selectedCategories[$category->id] ?? false) checked @endif>
                            <label class="form-check-label" for="category-{{ $category->id }}">{{ $category->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
