# 📝 Set Edit Template — Joomla 5/6 System Plugin

> Clean, distraction-free editing dashboard for frontend content editing.

Forces a specific template style and hides modules during frontend editing to create a focused workspace.

---

## 🚀 Key Features

- **🎨 Dedicated template style** for editing contexts
- **🎯 Configurable triggers** - specify components, views, layouts
- **📊 Module hiding** with multi-select interface
- **🎨 Custom CSS** for edit mode styling
- **🔧 Debug mode** to identify current context values
- **🛡️ Non-destructive** - no database changes

## ⚡ Configuration

**Basic Setup:**
1. Install and enable the plugin (https://github.com/RickR2H/setedittemplate/releases)
2. Set the Edit Template
3. Configure context triggers (components/views/layouts)
4. Select modules to hide during editing or when edit template is active
5. Add custom CSS if needed

**Context Detection:**
- **Components:** `com_content,com_k2` (empty = any)
- **Views:** `form,article,edit` (empty = any)
- **Layouts:** `edit,create` (empty = any)
- Enable debug mode to see current values

## 📦 Installation

`Extensions → Install → Upload Package File` → Enable plugin

## 🔧 Troubleshooting

**Template not switching:**
- Verify Edit Template Style exists
- Enable debug mode to check context matching

**Modules still appear:**
- Check modules are selected in "Disable modules on edit"
- Verify editor mode is active (use debug mode)

**Editor mode not activating:**
- Enable debug mode to see current component/view/layout values
- Ensure ALL configured context fields match (case-sensitive)

## 📅 Changelog

**v1.0.0** - Initial release

## 📄 License

GNU General Public License v2 or later.
