(function ($) {
    var currentTree;

    function loadJStree(index) {
        
        //Initialize unique jsTree for each hierarchy
        var groupingTree = $("[name='o:hierarchy[" + index + "][o:layout]']").siblings('.hierarchy-hierarchy-tree');
        var initialTreeData;
        groupingTree.jstree({
            'core': {
                "check_callback" : function (operation, node, parent, position, more) {
                    if(operation === "copy_node" || operation === "move_node") {
                        if(more.is_multi) {
                            return false; // prevent moving node to different tree
                        }
                    }
                    return true; // allow everything else
                },
                'force_text': true,
                'data': groupingTree.data('jstree-data'),
            },
            'plugins': ['removenode', 'editlink']
        }).on('loaded.jstree', function() {
            // Open all nodes by default.
            groupingTree.jstree(true).open_all();
            initialTreeData = JSON.stringify(groupingTree.jstree(true).get_json());
        }).on('move_node.jstree', function(e, data) {
            // Open node after moving it.
            var parent = groupingTree.jstree(true).get_node(data.parent);
            groupingTree.jstree(true).open_all(parent);
        });

        $('#site-form').on('o:before-form-unload', function () {
            if (initialTreeData !== JSON.stringify(groupingTree.jstree(true).get_json())) {
                Omeka.markDirty(this);
            }
        });
    }

    function replaceIndex(context, find, index) {
        context.find(':input').each(function() {
            var thisInput = $(this);
            if ($(this).attr('name') == undefined) {
                return;
            }
            var name = thisInput.attr('name').replace('[__' + find + '__]', '[' + index + ']');
            var label = thisInput.parents('.field').find('label').first();
            thisInput.attr('name', name);
            if (!thisInput.is(':hidden')) {
                thisInput.attr('id', name);
            }
            label.attr('for', name);
        });
    }

    $(document).ready(function () {
        var list = document.getElementById('hierarchies');
        var hierarchyIndex = 1;
        var jstreeIndex = 1;

        new Sortable(list, {
            draggable: ".block",
            handle: ".sortable-handle",
        });

        $('#new-hierarchy').on('click', '.hierarchy-add', function() {
            $.post(
                window.location.href,
                {layout: 'hierarchy'}
            ).done(function(data) {
                var newHierarchy = $(data).appendTo('#hierarchies');
                replaceIndex(newHierarchy, 'hierarchyIndex', hierarchyIndex);
                hierarchyIndex++;
                Omeka.scrollTo(newHierarchy);
            });
        });

        $('#hierarchies .block').each(function () {
            $(this).data('hierarchyIndex', hierarchyIndex);
            replaceIndex($(this), 'hierarchyIndex', hierarchyIndex);
            // loadJStree(hierarchyIndex);
            hierarchyIndex++;
        });

        $('#hierarchies').on('click', 'a.remove-value, a.restore-value', function (e) {
            e.preventDefault();
            var hierarchy = $(this).parents('.block');
            hierarchy.toggleClass('delete');
            hierarchy.find('a.remove-value, a.restore-value').removeClass('inactive');
            $(this).toggleClass('inactive');
            Omeka.markDirty($(this).closest('form'));
        });

        $('form').submit(function(e) {
            $('#hierarchies .hierarchy').each(function(hierarchyIndex) {
                var thisHierarchy = $(this);
                if (thisHierarchy.hasClass('delete')) {
                    thisHierarchy.find(':input').prop('disabled', true);
                }
            });
        });

        $('.collapse-all').on('click', function() {
            $('.block-header.collapse .collapse').click();
        });

        $('.expand-all').on('click', function() {
            $('.block-header:not(.collapse) .expand').click();
        });

        // Toggle block visibility
        $('#hierarchies').on('click', '.expand,.collapse', function() {
            var blockToggle = $(this);
            blockToggle.parents('.block-header').toggleClass('collapse');
        });

        $('#add-hierarchy').on(
            'keyup',
            '.page-selector-filter',
            (function() {
            var timer = 0;
            return function() {
                clearTimeout(timer);
                timer = setTimeout(filterPages.bind(this), 400);
            }
        })());
    });
})(window.jQuery);
