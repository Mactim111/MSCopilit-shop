# Copilot Instructions for MSCopilit-shop Project

## Workflow for Code Changes

**ALWAYS follow this workflow before implementing any code changes:**

### Step 1: Analyze and Present Options
When a user requests a code modification or new feature:
1. Understand the requirement completely
2. Identify 2-3 viable approaches/solutions
3. Present each option with:
   - Clear title/name
   - Detailed explanation of the approach
   - Pros and cons
   - Code example or snippet (if applicable)
   - Recommended option (if there's a clear winner)

### Step 2: Wait for User Approval
After presenting options:
- **PAUSE** and ask the user to choose
- Use the ask_user tool with choices corresponding to each option
- Do NOT proceed with implementation until user explicitly approves

### Step 3: Implement After Approval
Once user selects an option:
- Implement ONLY the chosen solution
- Apply changes to the codebase
- Run tests/validation as needed
- Confirm completion

## Exceptions (No need to show options)

Skip the "present options" workflow for:
- **Simple fixes** where only one obvious solution exists (e.g., fixing a typo, correcting a syntax error)
- **User explicitly asks for a specific approach** (e.g., "use method X from Model Y")
- **Quick information requests** (not code changes)

## Code Change Guidelines

- Make precise, complete changes — not partial solutions
- Update related files (tests, docs, configs) if affected
- Always validate that changes work before confirming completion
- Clean up temporary files after task completion

## File Naming Conventions

- **PHP Models**: `app/Models/` with PascalCase (e.g., `ProductVariant.php`)
- **Blade Templates**: `resources/views/` with kebab-case folders and filenames
- **CSS/JS**: `resources/css/` and `resources/js/` with appropriate naming

## Project Stack

- **Backend**: Laravel 11
- **Frontend**: Vue.js 3 + Vite
- **Database**: MySQL with migrations
- **Styling**: Tailwind CSS + custom CSS

---

**Remember**: Your goal is to help the user make informed decisions about their code. Always prioritize clarity and user control!
