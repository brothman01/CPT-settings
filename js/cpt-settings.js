            document.getElementById('add-type').addEventListener('click', function() {
                var container = document.getElementById('events-container');
                var index = container.children.length;
                var newEvent = `
                    <tr class="event">
                        <td>
                            <input type="text" name="events[${index}][single_name]" placeholder="Name (Singular)" style="width: 100%;" />
                        </td>
                        <td>
                            <input type="text" name="events[${index}][plural_name]" placeholder="Name (Plural)" style="width: 100%;" />
                        </td>
                        <td>
                            <input type="text" name="events[${index}][key]" placeholder="Key" style="width: 100%;" />
                        </td>
                        <td>
                            <input type="text" name="events[${index}][description]" placeholder="Description" style="width: 100%;" />
                        </td>
                        <td>
                            <input type="text" name="events[${index}][public]" placeholder="true/false" />
                        </td>
                        <td>
                            <input type="text" name="events[${index}][supports]" placeholder="e.g. title, editor, thumbnail" />
                        </td>
                        <td>
                            <input type="text" name="events[${index}][taxonomies]" placeholder="e.g. category, post_tag" />
                        </td>
                        <td>
                            <input type="text" name="events[${index}][icon]" placeholder="Icon URL" />
                        </td>
                        <td>
                            <button type="button" class="remove-event button">Remove</button>
                        </td>
                    </tr>`;
                container.insertAdjacentHTML('beforeend', newEvent);
            });

            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('remove-event')) {
                    e.target.closest('tr').remove();
                }
            });
