class Todo {
  constructor() {
    this.searchInput = document.querySelector("#search-input");
    this.checklist = document.querySelector("#checklist");
    this.taskForm = document.querySelector("#task-form");
    this.taskInput = document.querySelector("#text-input");
    this.dateInput = document.querySelector("#date-input");
    this.storageKey = "checklist-tasks";
    this.maxTaskLength = 255;
    this.tasks = this.loadTasks();

    this.bindEvents();
    this.draw();
  }

  loadTasks() {
    const storedTasks = localStorage.getItem(this.storageKey);
    if (storedTasks === null) {
      return [];
    }

    const tasks = JSON.parse(storedTasks);
    if (!Array.isArray(tasks) || tasks.some((task) =>
      task === null
      || typeof task !== "object"
      || typeof task.id !== "string"
      || typeof task.title !== "string"
      || typeof task.dueDate !== "string"
      || typeof task.completed !== "boolean")) {
      throw new Error("Saved checklist data has an invalid format.");
    }
    return tasks;
  }

  saveTasks() {
    localStorage.setItem(this.storageKey, JSON.stringify(this.tasks));
  }

  appendHighlightedText(element, text, query) {
    if (query.length < 2) {
      element.textContent = text;
      return;
    }

    const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    const matches = new RegExp(escapedQuery, "giu");
    let lastIndex = 0;
    let match = matches.exec(text);

    while (match) {
      element.append(document.createTextNode(text.slice(lastIndex, match.index)));
      const highlight = document.createElement("mark");
      highlight.textContent = match[0];
      element.append(highlight);
      lastIndex = match.index + match[0].length;
      match = matches.exec(text);
    }

    element.append(document.createTextNode(text.slice(lastIndex)));
  }

  draw() {
    this.checklist.replaceChildren();
    const searchTerm = this.searchInput.value.trim();
    const query = searchTerm.toLocaleLowerCase();

    this.tasks
      .filter((task) => query.length < 2
        || `${task.title} ${task.dueDate}`.toLocaleLowerCase().includes(query))
      .forEach((task) => {
        const item = document.createElement("div");
        item.className = "checklist-item";

        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.checked = task.completed;
        checkbox.setAttribute("aria-label", `Mark task completed: ${task.title}`);
        checkbox.addEventListener("change", () => this.toggle(task.id));

        const title = document.createElement("span");
        title.className = "task-title";
        this.appendHighlightedText(title, task.title, searchTerm);
        const date = document.createElement("span");
        date.className = "task-date";
        if (task.dueDate) {
          this.appendHighlightedText(date, ` (${task.dueDate})`, searchTerm);
        }

        item.append(checkbox, title, date);
        item.addEventListener("click", (event) => {
          if (!(event.target instanceof Element) || event.target.closest("input, button")) {
            return;
          }

          if (item.querySelector(".task-edit-input")) {
            return;
          }

          const editFields = document.createElement("span");
          const titleInput = document.createElement("input");
          titleInput.type = "text";
          titleInput.className = "task-edit-input";
          titleInput.value = task.title;
          titleInput.setAttribute("aria-label", "Edit task");

          const dateInput = document.createElement("input");
          dateInput.type = "text";
          dateInput.className = "task-edit-input";
          dateInput.value = task.dueDate;
          dateInput.placeholder = this.dateInput.placeholder;
          dateInput.inputMode = "numeric";
          dateInput.setAttribute("aria-label", "Edit due date");

          editFields.append(titleInput, dateInput);
          title.replaceWith(editFields);
          date.remove();

          titleInput.addEventListener("input", () => {
            titleInput.setCustomValidity(
              this.taskTitleValidationMessage(titleInput.value),
            );
          });
          dateInput.addEventListener("input", () => {
            dateInput.setCustomValidity(
              this.dateValidationMessage(dateInput.value.trim()),
            );
          });
          editFields.addEventListener("focusout", (focusEvent) => {
            if (focusEvent.relatedTarget instanceof Node
              && editFields.contains(focusEvent.relatedTarget)) {
              return;
            }

            const titleError = this.taskTitleValidationMessage(titleInput.value);
            const dateError = this.dateValidationMessage(dateInput.value.trim());
            titleInput.setCustomValidity(titleError);
            dateInput.setCustomValidity(dateError);
            if (titleError || dateError) {
              const invalidInput = titleError ? titleInput : dateInput;
              invalidInput.reportValidity();
              invalidInput.focus();
              return;
            }

            this.editTask(task.id, titleInput.value, dateInput.value);
          });

          titleInput.focus();
          titleInput.select();
        });

        const deleteButton = document.createElement("button");
        deleteButton.type = "button";
        deleteButton.textContent = "Delete";
        deleteButton.setAttribute("aria-label", `Delete task: ${task.title}`);
        deleteButton.addEventListener("click", () => {
          if (!window.confirm(`Delete task "${task.title}"?`)) {
            return;
          }
          this.remove(task.id);
        });

        item.append(deleteButton);
        this.checklist.append(item, document.createElement("br"));
      });
  }

  add(title, dueDate = "") {
    const validationMessage = this.taskTitleValidationMessage(title);
    if (validationMessage) {
      this.taskInput.setCustomValidity(validationMessage);
      this.taskInput.reportValidity();
      return false;
    }

    const dateValidation = this.dateValidationMessage(dueDate);
    if (dateValidation) {
      this.dateInput.setCustomValidity(dateValidation);
      this.dateInput.reportValidity();
      return false;
    }

    this.tasks.push({
      id: `${Date.now()}-${Math.random()}`,
      title: title.trim(),
      dueDate: dueDate.trim(),
      completed: false,
    });
    this.saveTasks();
    this.draw();
    return true;
  }

  remove(id) {
    const taskCount = this.tasks.length;
    this.tasks = this.tasks.filter((task) => task.id !== id);
    if (this.tasks.length === taskCount) {
      return false;
    }
    this.saveTasks();
    this.draw();
    return true;
  }

  edit(id, title) {
    const task = this.tasks.find((savedTask) => savedTask.id === id);
    if (!task) {
      return false;
    }
    return this.editTask(id, title, task.dueDate);
  }

  editDate(id, dueDate) {
    const task = this.tasks.find((savedTask) => savedTask.id === id);
    if (!task) {
      return false;
    }
    return this.editTask(id, task.title, dueDate);
  }

  editTask(id, title, dueDate) {
    if (this.taskTitleValidationMessage(title)
      || this.dateValidationMessage(dueDate.trim())) {
      return false;
    }

    const task = this.tasks.find((savedTask) => savedTask.id === id);
    if (!task) {
      return false;
    }
    task.title = title.trim();
    task.dueDate = dueDate.trim();
    this.saveTasks();
    this.draw();
    return true;
  }

  toggle(id) {
    const task = this.tasks.find((savedTask) => savedTask.id === id);
    if (!task) {
      return false;
    }
    task.completed = !task.completed;
    this.saveTasks();
    this.draw();
    return true;
  }

  taskTitleValidationMessage(value) {
    const title = value.trim();
    if (title.length < 3) {
      return "Task must contain at least 3 characters.";
    }
    if (value.length > this.maxTaskLength) {
      return `Task must contain no more than ${this.maxTaskLength} characters.`;
    }
    return "";
  }

  dateValidationMessage(value) {
    if (!value) {
      return "";
    }

    const match = /^(\d{2})-(\d{2})-(\d{4})$/.exec(value);
    if (!match) {
      return "Enter a date in DD-MM-YYYY format.";
    }

    const day = Number(match[1]);
    const month = Number(match[2]);
    const year = Number(match[3]);
    const dueDate = new Date(0);
    dueDate.setHours(0, 0, 0, 0);
    dueDate.setFullYear(year, month - 1, day);

    if (dueDate.getFullYear() !== year
      || dueDate.getMonth() !== month - 1
      || dueDate.getDate() !== day) {
      return "Enter a valid calendar date.";
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    if (dueDate <= today) {
      return "The date must be in the future.";
    }

    return "";
  }

  bindEvents() {
    this.searchInput.addEventListener("input", () => this.draw());
    this.taskInput.addEventListener("input", () => {
      this.taskInput.setCustomValidity(
        this.taskTitleValidationMessage(this.taskInput.value),
      );
    });
    this.dateInput.addEventListener("input", () => this.dateInput.setCustomValidity(""));

    this.taskForm.addEventListener("submit", (event) => {
      event.preventDefault();
      const added = this.add(this.taskInput.value, this.dateInput.value);
      if (added) {
        this.taskForm.reset();
      }
    });
  }
}

document.todo = new Todo();
