@props([
    'node',
    'modality',
    'ear',
    'namePrefix',
    'selectedKeys' => [],
    'parentPath' => null,
    'depth' => 0,
])

@php
    use App\Support\ImagingFindingsTreeSupport;

    $path = $parentPath === null
        ? (string) ($node['key'] ?? '')
        : $parentPath.'.'.($node['key'] ?? '');
    $children = $node['children'] ?? [];
    $hasChildren = $children !== [];
    $isLeaf = ! $hasChildren;
    $label = __((string) ($node['label_key'] ?? $path));
    $checkState = ImagingFindingsTreeSupport::nodeCheckState($modality, $path, $selectedKeys);
    $inputId = str_replace(['.', '[', ']'], '_', "{$namePrefix}_{$ear}_{$modality}_{$path}");
@endphp

<div class="imaging-tree-node{{ $depth > 0 ? ' imaging-tree-node--nested' : '' }}"
     data-imaging-tree-node
     data-path="{{ $path }}"
     data-depth="{{ $depth }}">
    <div class="imaging-tree-node__row">
        @if($hasChildren)
            <button type="button"
                    class="imaging-tree-node__toggle"
                    data-imaging-tree-toggle
                    aria-expanded="true"
                    aria-label="{{ __('workflow.imaging_tree.toggle_group') }}">
                <i class="ti ti-chevron-down"></i>
            </button>
        @else
            <span class="imaging-tree-node__toggle-spacer" aria-hidden="true"></span>
        @endif

        <div class="form-check imaging-tree-node__check mb-0">
            <input type="checkbox"
                   class="form-check-input imaging-tree-node__checkbox"
                   id="{{ $inputId }}"
                   data-imaging-tree-checkbox
                   data-modality="{{ $modality }}"
                   data-ear="{{ $ear }}"
                   data-path="{{ $path }}"
                   data-has-children="{{ $hasChildren ? '1' : '0' }}"
                   data-label="{{ $label }}"
                   @if($isLeaf)
                       name="{{ $namePrefix }}[{{ $ear }}][{{ $modality }}][]"
                       value="{{ $path }}"
                       data-breadcrumb="{{ ImagingFindingsTreeSupport::breadcrumbLabel($modality, $path) }}"
                   @endif
                   @checked($checkState === 'checked')
                   @if($checkState === 'indeterminate') data-indeterminate="1" @endif>
            <label class="form-check-label" for="{{ $inputId }}">{{ $label }}</label>
        </div>
    </div>

    @if($hasChildren)
        <div class="imaging-tree-node__children" data-imaging-tree-children>
            @foreach($children as $child)
                @include('components.partials.imaging-tree-node', [
                    'node' => $child,
                    'modality' => $modality,
                    'ear' => $ear,
                    'namePrefix' => $namePrefix,
                    'selectedKeys' => $selectedKeys,
                    'parentPath' => $path,
                    'depth' => $depth + 1,
                ])
            @endforeach
        </div>
    @endif
</div>
